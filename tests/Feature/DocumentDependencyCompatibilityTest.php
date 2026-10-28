<?php

use App\Livewire\Documents\CreateDocument;
use App\Livewire\Documents\ViewDocument;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use setasign\Fpdi\Fpdi;

beforeEach(function () {
    $officeId = DB::table('offices')->insertGetId(['name' => 'Test Office', 'abbreviation' => 'TEST', 'office_type' => 'ADMIN']);
    $roleId = DB::table('roles')->insertGetId(['role' => 'Administrator', 'description' => 'Test role']);
    $this->author = User::create(['name' => 'Test Author', 'email' => 'author@example.test', 'password' => 'password', 'office_id' => $officeId, 'role_id' => $roleId]);
    DB::table('offices')->where('id', $officeId)->update(['head_id' => $this->author->id]);
    DB::table('user_profiles')->insert(['user_id' => $this->author->id, 'given_name' => 'Test', 'family_name' => 'Author']);
    $this->typeId = DB::table('document_types')->insertGetId(['name' => 'Inter-Office Memorandum', 'abbreviation' => 'IOM']);
    DB::table('role_document_types')->insert(['role_id' => $roleId, 'document_type_id' => $this->typeId, 'is_allowed' => true]);
    $this->actingAs($this->author);
});

it('creates and edits a formatted draft through Livewire', function () {
    Livewire::test(CreateDocument::class)
        ->set('document_type_id', $this->typeId)
        ->set('document_type', 'IOM')
        ->set('document_to_id', $this->author->office_id)
        ->set('subject', 'Compatibility draft')
        ->set('content', '<p class="ql-align-center"><strong>Draft body</strong></p>')
        ->call('submitDocument', 'draft')
        ->assertHasNoErrors()
        ->assertRedirect(route('documents.list-documents', ['mode' => 'sent']));

    $document = Document::firstOrFail();
    expect($document->status)->toBe('draft');
    expect($document->content)->toContain('ql-align-center', '<strong>Draft body</strong>');

    Livewire::test(CreateDocument::class, ['draft_id' => $document->id])
        ->assertSet('subject', 'Compatibility draft')
        ->set('subject', 'Edited draft')
        ->call('submitDocument', 'draft')
        ->assertHasNoErrors();

    expect(Document::count())->toBe(1);
    expect($document->fresh()->subject)->toBe('Edited draft');
});

it('renders the real document template as a PDF readable by FPDI', function () {
    $data = [
        'action' => 'preview', 'documentType' => 'Inter-Office Memorandum',
        'documentNumber' => 'TEST-IOM-1', 'date_sent' => now(),
        'subject' => 'Compatibility preview', 'content' => '<p><strong>PDF body</strong></p>',
        'toName' => 'Recipient', 'toPosition' => 'Head', 'fromName' => 'Author',
        'fromPosition' => 'Head', 'thru' => null, 'unit' => 'TEST',
        'signatories' => '[]', 'cfs' => '[]',
    ];
    $response = $this->withSession(['compatibility-preview' => $data])
        ->get('/document/preview?compatibility-preview');
    $response->assertOk()->assertHeader('Content-Type', 'application/pdf');

    $path = $response->baseResponse->getFile()->getPathname();
    expect(file_get_contents($path, false, null, 0, 5))->toBe('%PDF-');
    $pdf = new Fpdi;
    expect($pdf->setSourceFile($path))->toBeGreaterThan(0);
    $pdf->AddPage();
    $pdf->useTemplate($pdf->importPage(1));
    expect($pdf->Output('S'))->toStartWith('%PDF-');
});

it('sends an IOM and records its signatory approval with email notifications', function () {
    // The current IOM flow assigns user ID 2 as its approver.
    $approver = User::create(['name' => 'Approver', 'email' => 'approver@example.test', 'password' => 'password', 'office_id' => $this->author->office_id, 'role_id' => $this->author->role_id]);
    expect($approver->id)->toBe(2);

    Livewire::test(CreateDocument::class)
        ->set('document_type_id', $this->typeId)
        ->set('document_type', 'IOM')
        ->set('document_to_id', $this->author->office_id)
        ->set('subject', 'Approval compatibility')
        ->set('content', '<p><strong>Approval body</strong></p>')
        ->call('submitDocument', 'send')
        ->assertHasNoErrors();

    $document = Document::firstOrFail();
    expect($document->status)->toBe('sent');
    expect($document->document_number)->not->toBeEmpty();
    expect($document->logs()->where('action', 'sent')->exists())->toBeTrue();
    expect($document->signatories()->firstOrFail()->user_id)->toBe($approver->id);

    $this->actingAs($approver);
    Livewire::test(ViewDocument::class, ['number' => $document->document_number])
        ->call('documentSigned')
        ->assertDispatched('fireSwal');

    expect($document->signatories()->firstOrFail()->signed_at)->not->toBeNull();
    expect($document->logs()->where('action', 'signed')->exists())->toBeTrue();
    // Use the array transport: exercise Symfony Mailer and Markdown rendering without sending externally.
    expect(app('mailer')->getSymfonyTransport()->messages())->toHaveCount(2);
});
