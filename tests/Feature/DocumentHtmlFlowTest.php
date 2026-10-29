<?php

use App\Livewire\Documents\CreateDocument;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\DocumentPreviewDataService;
use Livewire\Livewire;
use setasign\Fpdi\Fpdi;

beforeEach(function () {
    $role = Role::create(['role' => 'html-test-sender', 'description' => 'Test sender']);
    $role->permissions()->attach(Permission::where('key', 'send_documents')->firstOrFail());
    $office = Office::create(['name' => 'Test Office', 'abbreviation' => 'TEST', 'office_type' => 'ADMIN']);
    $this->author = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
    $office->update(['head_id' => $this->author->id]);
    $this->type = DocumentType::create([
        'name' => 'Test Memorandum', 'abbreviation' => 'TEST', 'recipient_mode' => 'none',
        'is_publicly_creatable' => true, 'requires_signatories' => false,
        'content_template' => '<p><strong>{TO}</strong></p><p>Subject: {SUBJECT}</p>',
    ]);
    $this->actingAs($this->author);
});

it('saves edits and sends a formatted draft through the existing Livewire flow', function () {
    $html = '<p class="ql-align-center"><strong>Draft body</strong><img src=x onerror="alert(1)"></p>';
    Livewire::test(CreateDocument::class)
        ->set('document_type_id', (string) $this->type->id)
        ->call('handleUpdateDocumentType')
        ->set('subject', 'Compatibility draft')
        ->set('content', $html)
        ->call('submitDocument', 'draft')
        ->assertHasNoErrors()
        ->assertRedirect(route('documents.list-documents', ['mode' => 'Sent']));

    $document = Document::firstOrFail();
    expect($document->status)->toBe('Draft');
    expect($document->getRawOriginal('content'))->toBe('<p class="ql-align-center"><strong>Draft body</strong></p>');

    Livewire::test(CreateDocument::class, ['draft_id' => $document->id])
        ->assertSet('content', $document->content)
        ->set('subject', 'Edited draft')
        ->call('submitDocument', 'send')
        ->assertHasNoErrors();

    expect(Document::count())->toBe(1);
    expect($document->fresh()->subject)->toBe('Edited draft');
    expect($document->fresh()->status)->toBe('Sent');
    expect($document->fresh()->document_number)->not->toBeEmpty();
});

it('escapes placeholder text before inserting it into configured HTML', function () {
    Livewire::test(CreateDocument::class)
        ->set('document_type_id', (string) $this->type->id)
        ->set('document_to_text', '<img src=x onerror=alert(1)>')
        ->set('subject', '<script>alert(1)</script>')
        ->call('updateContentWithSubject')
        ->assertDispatched('update-quill', function ($event, $params) {
            $html = $params[0]['content'];
            expect($html)->toContain('<strong>', '&lt;IMG', '&lt;SCRIPT&gt;');
            expect($html)->not->toContain('<img', '<script', '<IMG', '<SCRIPT');

            return true;
        });
});

it('sanitizes unsaved preview content at the PDF rendering boundary and produces an importable PDF', function () {
    $document = Document::create([
        'document_number' => 'TEST-1', 'document_type_id' => $this->type->id,
        'from_id' => $this->author->office_id, 'created_by' => $this->author->id,
        'subject' => 'Preview', 'content' => '<p><strong>PDF body</strong></p>', 'status' => 'Draft',
    ]);
    $data = app(DocumentPreviewDataService::class)->build($document, 'preview');
    $data['content'] = '<p><strong>PDF body</strong><img src=x onerror="alert(1)"></p>';
    $renderData = $data;
    foreach (['document', 'signatories', 'attachments', 'cfs'] as $field) {
        if (isset($renderData[$field]) && is_string($renderData[$field])) {
            $renderData[$field] = json_decode($renderData[$field], true);
        }
    }
    $rendered = view('pdf.document-preview', $renderData)->render();
    expect($rendered)->toContain('<strong>PDF body</strong>');
    expect($rendered)->not->toContain('onerror=', 'src=x');

    $response = $this->withSession(['html-preview' => $data])->get('/document/preview?html-preview');
    $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
    $pdf = new Fpdi;
    expect($pdf->setSourceFile($response->baseResponse->getFile()->getPathname()))->toBeGreaterThan(0);
    $pdf->AddPage();
    $pdf->useTemplate($pdf->importPage(1));
    expect($pdf->Output('S'))->toStartWith('%PDF-');
});
