<?php

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\User;
use App\Services\DocumentPreviewDataService;
use App\Support\IndorsementHeader;
use Illuminate\Support\Facades\View;
use setasign\Fpdi\Fpdi;

beforeEach(function () {
    $office = Office::create(['name' => 'Information and Communications Technology Unit', 'abbreviation' => 'ICTU']);
    $this->author = User::factory()->create(['office_id' => $office->id]);
    $office->update(['head_id' => $this->author->id]);
    $this->type = DocumentType::create(['name' => 'Indorsement Letter', 'abbreviation' => 'IL', 'print_layout' => 'indorsement']);
    $this->original = Document::create([
        'document_number' => 'ICTU(ADMIN)-IL-1-2026', 'from_id' => $office->id,
        'document_type_id' => $this->type->id, 'created_by' => $this->author->id,
        'subject' => 'Header layout', 'content' => '<p>Letter body</p>', 'date_sent' => '2026-10-01', 'status' => 'Sent',
    ]);
    $this->actingAs($this->author);
});

it('fills original IL metadata from document dates', function () {
    $data = app(DocumentPreviewDataService::class)->build($this->original);
    $data['document'] = json_decode($data['document'], true);
    expect(IndorsementHeader::applies($data))->toBeTrue();
    expect(IndorsementHeader::data($data))->toBe([
        'ilEffectiveDate' => 'Oct 01, 2026', 'ilRevisionCode' => '00', 'ilRevisionDate' => 'N/A',
    ]);
});

it('uses the original issue date and the current revision date for revised ILs', function () {
    $revision = $this->original->replicate(['document_number']);
    $revision->fill(['document_number' => 'ICTU(ADMIN)-IL-1-2026-REV1', 'is_revision' => true,
        'original_document_id' => $this->original->id, 'date_sent' => '2026-10-29'])->save();
    $data = app(DocumentPreviewDataService::class)->build($revision);
    $data['document'] = json_decode($data['document'], true);
    expect(IndorsementHeader::data($data))->toBe([
        'ilEffectiveDate' => 'Oct 01, 2026', 'ilRevisionCode' => '01', 'ilRevisionDate' => 'Oct 29, 2026',
    ]);
    expect(IndorsementHeader::data(['indorsementRevisionSourceId' => $this->original->id, 'date_sent' => '2026-10-30']))
        ->toBe(['ilEffectiveDate' => 'Oct 01, 2026', 'ilRevisionCode' => '02', 'ilRevisionDate' => 'Oct 30, 2026']);
});

it('does not apply the new header to any other document type even with the same layout', function () {
    $other = DocumentType::create(['name' => 'Other Letter', 'abbreviation' => 'OTHER', 'print_layout' => 'indorsement']);
    $this->original->update(['document_type_id' => $other->id]);
    $data = app(DocumentPreviewDataService::class)->build($this->original->fresh());
    $data['document'] = json_decode($data['document'], true);
    expect(IndorsementHeader::applies($data))->toBeFalse();
    foreach (['memorandum', 'letter', 'special_order'] as $layout) {
        expect(IndorsementHeader::applies(['printLayout' => $layout, 'documentType' => 'Indorsement Letter']))->toBeFalse();
    }
});

it('reports the real page count in IL headers for short and long documents', function ($paragraphs) {
    $this->original->update(['content' => str_repeat('<p>Indorsement content for pagination verification.</p>', $paragraphs)]);
    $data = app(DocumentPreviewDataService::class)->build($this->original->fresh());
    $rendered = [];
    View::composer('pdf.document-preview', function ($view) use (&$rendered) {
        $rendered[] = $view->getData();
    });
    $response = $this->withSession(['il-header' => $data])->get('/document/preview?il-header');
    $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
    $pdf = new Fpdi;
    $pages = $pdf->setSourceFile($response->baseResponse->getFile()->getPathname());
    $final = end($rendered);
    expect($final['useIlHeader'])->toBeTrue();
    expect($final['ilPageCount'])->toBe($pages);
    expect($paragraphs > 1 ? $pages > 1 : $pages === 1)->toBeTrue();
    expect($final['ilRevisionCode'])->toBe('00');
})->with([1, 100]);

it('keeps other document previews on their existing rendering path', function () {
    $other = DocumentType::create(['name' => 'Other Letter', 'abbreviation' => 'OTHER', 'print_layout' => 'indorsement']);
    $this->original->update(['document_type_id' => $other->id]);
    $data = app(DocumentPreviewDataService::class)->build($this->original->fresh());
    $rendered = [];
    View::composer('pdf.document-preview', function ($view) use (&$rendered) {
        $rendered[] = $view->getData();
    });
    $this->withSession(['other-header' => $data])->get('/document/preview?other-header')->assertOk();
    expect($rendered)->toHaveCount(1);
    expect($rendered[0])->not->toHaveKey('useIlHeader');
});
