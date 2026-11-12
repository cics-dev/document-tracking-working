<?php

use App\Models\{Document, DocumentType, Office, Permission, Role, User};
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

beforeEach(function () {
    $role = Role::create(['role' => 'summary-pdf-sender', 'description' => 'Sender']);
    $role->permissions()->attach(Permission::where('key', 'send_documents')->firstOrFail());
    $office = Office::create(['name' => 'Information and Communications Technology Unit', 'abbreviation' => 'ICTU', 'office_type' => 'ADMIN']);
    $this->sender = User::factory()->create(['office_id' => $office->id, 'role_id' => $role->id]);
    $office->update(['head_id' => $this->sender->id]);
    $type = DocumentType::create(['name' => 'Request Letter Memorandum', 'abbreviation' => 'RLM']);
    $this->document = Document::create(['document_number' => 'ICTU(ADMIN)-RLM-4-2026', 'from_id' => $office->id, 'to_id' => $office->id, 'document_type_id' => $type->id, 'created_by' => $this->sender->id, 'subject' => 'Request for office supplies and equipment', 'content' => 'Private original content', 'status' => 'Sent']);
    foreach (['Reviewed by', 'Approved by', 'Generation of IOM'] as $index => $label) {
        $this->document->steps()->create(['office_id' => $office->id, 'user_id' => $this->sender->id, 'step_type' => $index === 2 ? 'action' : 'signatory', 'step_label' => $label, 'sequence' => $index, 'status' => 'Pending']);
    }
    foreach (['Document Sent', 'Office viewed the document'] as $label) {
        $this->document->logs()->create(['user_id' => $this->sender->id, 'action' => 'Viewed', 'description' => $label]);
    }
    $this->url = route('documents.tracking-summary', $this->document);
});

it('generates a single-page A4 summary PDF for a normal workflow', function () {
    $response = $this->actingAs($this->sender)->get($this->url)->assertOk()->assertHeader('content-type', 'application/pdf');
    expect($response->getContent())->toStartWith('%PDF-');
    $reader = new Fpdi;
    expect($reader->setSourceFile(StreamReader::createByString($response->getContent())))->toBe(1);
    $reader->AddPage();
    $size = $reader->getTemplateSize($reader->importPage(1));
    expect(round($size['width']))->toBe(210.0);
    expect(round($size['height']))->toBe(297.0);
    if ($samplePath = getenv('TRACKING_PDF_SAMPLE')) file_put_contents($samplePath, $response->getContent());
});

it('keeps the summary PDF behind document authorization', function () {
    $this->get($this->url)->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get($this->url)->assertForbidden();
});

it('paginates longer summaries into valid PDF pages', function () {
    foreach (range(1, 10) as $i) {
        $this->document->logs()->create(['user_id' => $this->sender->id, 'action' => 'Reviewed', 'description' => str_repeat('A detailed review was recorded for this workflow stage. ', 18)]);
    }
    $response = $this->actingAs($this->sender)->get($this->url)->assertOk()->assertHeader('content-type', 'application/pdf');
    $reader = new Fpdi;
    $pages = $reader->setSourceFile(StreamReader::createByString($response->getContent()));
    expect($pages)->toBeGreaterThan(1)->toBeLessThan(8);
    $reader->AddPage();
    $reader->importPage($pages);
    if ($samplePath = getenv('TRACKING_PDF_SAMPLE')) file_put_contents(str_replace('.pdf', '-long.pdf', $samplePath), $response->getContent());
});
