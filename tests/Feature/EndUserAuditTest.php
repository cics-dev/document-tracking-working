<?php

use App\Livewire\Documents\{CreateDocument, ListDocuments};
use App\Models\{Document, DocumentType, Office, Permission, Role, User};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

// Reproduction probes: passing means the described end-user problem exists.
// Only visible form fields, displayed buttons and ordinary page routes are used.
beforeEach(function () {
    Storage::fake('public');
    $role = Role::create(['role' => 'ordinary-audit-sender', 'description' => 'Sender']);
    $role->permissions()->attach(Permission::where('key', 'send_documents')->firstOrFail());
    $this->office = Office::create(['name' => 'Ordinary Office', 'abbreviation' => 'ORD', 'office_type' => 'ADMIN']);
    $this->actor = User::factory()->create(['role_id' => $role->id, 'office_id' => $this->office->id]);
    $this->office->update(['head_id' => $this->actor->id]);
    $this->type = DocumentType::create(['name' => 'Ordinary Letter', 'abbreviation' => 'OL', 'recipient_mode' => 'text', 'is_publicly_creatable' => true, 'requires_signatories' => false, 'allow_attachments' => true, 'show_carbon_copy' => true]);
    $this->actingAs($this->actor);
});

it('reproduces uploaded attachments disappearing after saving and reopening a draft', function () {
    Livewire::test(CreateDocument::class)
        ->set('document_type_id', (string) $this->type->id)->call('handleUpdateDocumentType')
        ->set('subject', 'Draft with file')->set('document_to_text', 'Outside agency')->set('content', 'Body')
        ->set('attachments', [UploadedFile::fake()->image('evidence.png')])
        ->call('submitDocument', 'Draft')->assertHasNoErrors();
    $draft = Document::where('subject', 'Draft with file')->firstOrFail();
    expect($draft->attachments()->count())->toBe(0);
    Livewire::test(CreateDocument::class, ['draft_id' => $draft->id])
        ->assertSet('existingAttachments', [])->assertSet('attachments', []);
});

it('reproduces the typed recipient disappearing when a draft is reopened', function () {
    Livewire::test(CreateDocument::class)
        ->set('document_type_id', (string) $this->type->id)->call('handleUpdateDocumentType')
        ->set('subject', 'Draft recipient')->set('document_to_text', 'Outside agency')->set('content', 'Body')
        ->call('submitDocument', 'Draft')->assertHasNoErrors();
    $draft = Document::where('subject', 'Draft recipient')->firstOrFail();
    expect($draft->to_text)->toBe('Outside agency');
    Livewire::test(CreateDocument::class, ['draft_id' => $draft->id])->assertSet('document_to_text', '');
});

it('keeps drafts unnumbered and offers Edit without View', function () {
    Livewire::test(CreateDocument::class)
        ->set('document_type_id', (string) $this->type->id)->set('subject', 'Unnumbered draft')
        ->call('submitDocument', 'Draft')->assertHasNoErrors();
    $draft = Document::where('subject', 'Unnumbered draft')->firstOrFail();
    expect($draft->document_number)->toBeNull();
    Livewire::test(ListDocuments::class, ['mode' => 'Sent'])
        ->assertSee('Unnumbered draft')->assertSee(route('documents.edit-draft', $draft->id), false)
        ->assertDontSee('wire:click="viewDocument(', false);
});

it('keeps a draft unnumbered even when manual number fields were filled', function () {
    Livewire::test(CreateDocument::class)
        ->set('document_type_id', (string) $this->type->id)->call('handleUpdateDocumentType')
        ->set('subject', 'Manual draft')->set('is_manual_document_number', true)
        ->set('manual_document_sequence', '42')->set('manual_document_year', '2026')
        ->call('submitDocument', 'Draft')->assertHasNoErrors();
    $draft = Document::where('subject', 'Manual draft')->firstOrFail();
    expect($draft->document_number)->toBeNull();
    Livewire::test(CreateDocument::class, ['draft_id' => $draft->id])
        ->assertSet('is_manual_document_number', false)->assertSet('manual_document_sequence', '')
        ->call('previewDocument')->assertDispatched('open-preview-tab');
    $preview = collect(session()->all())->first(fn ($value) => is_array($value) && ($value['action'] ?? null) === 'preview');
    expect(json_encode($preview))->not->toContain('ORD-OL-42-2026');
});

it('reproduces clicking remove on an existing attachment failing because its action is missing', function () {
    $draft = Document::create(['document_number' => 'ORD-OL-4-2026', 'document_type_id' => $this->type->id, 'from_id' => $this->office->id, 'created_by' => $this->actor->id, 'subject' => 'Existing attachment', 'content' => 'Body', 'status' => 'Draft']);
    $attachment = $draft->attachments()->create(['name' => 'file.pdf', 'file_url' => 'attachments/file.pdf', 'file_type' => 'pdf', 'is_upload' => true]);
    $component = Livewire::test(CreateDocument::class, ['draft_id' => $draft->id])->assertSee('removeExistingAttachment('.$attachment->id.')', false);
    expect(fn () => $component->call('removeExistingAttachment', $attachment->id))->toThrow(\Livewire\Exceptions\MethodNotFoundException::class);
});

it('previews a linked document while revising and allows the preview to reload', function () {
    $source = Document::create(['document_number' => 'ORD-OL-5-2026', 'document_type_id' => $this->type->id, 'from_id' => $this->office->id, 'created_by' => $this->actor->id, 'subject' => 'Source', 'content' => 'Body', 'status' => 'Approved']);
    $returned = Document::create(['document_number' => 'ORD-OL-6-2026', 'document_type_id' => $this->type->id, 'from_id' => $this->office->id, 'created_by' => $this->actor->id, 'subject' => 'Returned for revision', 'content' => 'Body', 'status' => 'Returned']);
    $attachment = $returned->attachments()->create(['name' => 'Attached source', 'attachment_document_id' => $source->id, 'file_type' => 'pdf', 'is_upload' => false]);
    $component = Livewire::test(CreateDocument::class, ['number' => $returned->document_number])->assertSee('Attached source');
    $component->call('viewAttachment', $attachment->id, 'internal')->assertHasNoErrors();
    $url = $component->get('attachmentPreviewUrl');
    expect($url)->toStartWith('/document/preview?');
    $this->get($url)->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->get($url)->assertOk()->assertHeader('content-type', 'application/pdf');
});

it('hides another offices confidential history on the normal dashboard', function () {
    $otherOffice = Office::create(['name' => 'Private Office', 'abbreviation' => 'PRV', 'office_type' => 'ADMIN']);
    $other = User::factory()->create(['office_id' => $otherOffice->id]);
    $private = Document::create(['document_number' => 'PRIVATE-OTHER-OFFICE-1', 'document_type_id' => $this->type->id, 'from_id' => $otherOffice->id, 'created_by' => $other->id, 'subject' => 'Private', 'content' => 'Body', 'status' => 'Sent']);
    $private->logs()->create(['user_id' => $other->id, 'action' => 'Sent', 'description' => 'CONFIDENTIAL ACTIVITY DESCRIPTION']);
    expect(app(\App\Services\DocumentQueryService::class)->canView($this->actor, $private->document_number))->toBeFalse();
    $this->get(route('dashboard'))->assertOk()->assertDontSee('PRIVATE-OTHER-OFFICE-1')->assertDontSee('CONFIDENTIAL ACTIVITY DESCRIPTION');
});

it('checks ordinary sender routes and hides inaccessible navigation entries', function () {
    foreach (['dashboard', 'settings.profile', 'settings.password', 'settings.two-factor', 'settings.appearance', 'settings.connection', 'settings.office', 'documents.create-document', 'documents.archive-physical'] as $name) {
        $this->get(route($name))->assertOk();
    }
    $this->get(route('documents.list-documents', 'Sent'))->assertOk();
    foreach (['roles', 'access-rights', 'document-flows', 'document-types', 'users.list-users', 'users.create-user', 'offices.list-offices', 'offices.create-office', 'documents.list-external-documents', 'documents.receive-external-document'] as $name) {
        $this->get(route($name))->assertForbidden();
    }
    foreach (['received', 'all'] as $mode) {
        $this->get(route('documents.list-documents', $mode))->assertForbidden();
    }
    $this->get(route('dashboard'))->assertDontSee('href="'.route('roles').'"', false)
        ->assertDontSee('href="'.route('users.list-users').'"', false)
        ->assertDontSee('href="'.route('documents.list-documents', 'all').'"', false);
});

it('allows a normal PDF preview to reload and still rejects a missing preview', function () {
    $component = Livewire::test(CreateDocument::class)
        ->set('document_type_id', (string) $this->type->id)->call('handleUpdateDocumentType')
        ->set('subject', 'Preview refresh')->set('document_to_text', 'Agency')->set('content', 'Body')
        ->call('previewDocument')->assertDispatched('open-preview-tab');
    $key = collect(session()->all())->search(fn ($value) => is_array($value) && ($value['action'] ?? null) === 'preview');
    expect($key)->toBeString();
    $this->get('/document/preview?'.$key)->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->get('/document/preview?'.$key)->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->get('/document/preview?nonexistent-preview')->assertNotFound();
});

it('reproduces carbon-copy selections disappearing after saving and reopening a draft', function () {
    $copyOffice = Office::create(['name' => 'Copy Office', 'abbreviation' => 'COPY', 'office_type' => 'ADMIN']);
    $head = User::factory()->create(['office_id' => $copyOffice->id]);
    $copyOffice->update(['head_id' => $head->id]);
    Livewire::test(CreateDocument::class)
        ->set('document_type_id', (string) $this->type->id)->call('handleUpdateDocumentType')
        ->set('subject', 'Draft copies')->set('selected_cf_office', (string) $copyOffice->id)
        ->call('addCfOffice')->assertSet('cf_offices', [$copyOffice->id])
        ->call('submitDocument', 'Draft')->assertHasNoErrors();
    $draft = Document::where('subject', 'Draft copies')->firstOrFail();
    Livewire::test(CreateDocument::class, ['draft_id' => $draft->id])->assertSet('cf_offices', []);
});

it('assigns an automatic number on Send without drafts reserving that number', function () {
    Livewire::test(CreateDocument::class)
        ->set('document_type_id', (string) $this->type->id)->call('handleUpdateDocumentType')
        ->set('subject', 'Reserved manual number')->set('is_manual_document_number', true)
        ->set('manual_document_sequence', '1')->set('manual_document_year', (string) date('Y'))
        ->call('submitDocument', 'Draft')->assertHasNoErrors();
    $component = Livewire::test(CreateDocument::class)
        ->set('document_type_id', (string) $this->type->id)->call('handleUpdateDocumentType')
        ->set('subject', 'Normal automatic send')->set('document_to_text', 'Agency')->set('content', 'Body');
    $component->call('submitDocument', 'send')->assertHasNoErrors();
    expect(Document::where('subject', 'Reserved manual number')->firstOrFail()->document_number)->toBeNull();
    $sent = Document::where('subject', 'Normal automatic send')->firstOrFail();
    expect($sent->document_number)->toBe('ORD-OL-1-'.date('Y'));
    Livewire::test(ListDocuments::class, ['mode' => 'Sent'])->assertSee("viewDocument('".$sent->document_number."')", false);
});

it('checks the external response redirect with and without internal send permission', function (bool $canSendInternal) {
    $this->actor->role->permissions()->detach();
    $keys = $canSendInternal ? ['send_external_documents', 'send_documents'] : ['send_external_documents'];
    $this->actor->role->permissions()->attach(Permission::whereIn('key', $keys)->pluck('id'));
    $external = \App\Models\ExternalDocument::create(['document_number' => 'EXT-ORD-1', 'from' => 'Agency', 'to_id' => $this->office->id, 'subject' => 'Incoming request', 'received_date' => now(), 'file_url' => 'attachments/incoming.pdf', 'file_type' => 'pdf']);
    $rule = \App\Models\DocumentGenerationRule::create(['source_context' => 'external', 'target_document_type_id' => $this->type->id, 'button_label' => 'Write response', 'requires_assigned_office' => true]);
    $rule->roles()->attach($this->actor->role_id);
    Livewire::test(\App\Livewire\Documents\ViewExternalDocument::class, ['id' => $external->id])
        ->assertSee('Write response')->call('generateDocument', $rule->id)
        ->assertRedirect(route('documents.create-document'));
    Livewire::test(CreateDocument::class)->set('content', 'Response body')->call('submitDocument', 'send')
        ->assertHasNoErrors()->assertRedirect(route('documents.list-documents', 'Sent'));
    expect(Document::where('subject', 'RE: Incoming request')->where('status', 'Sent')->exists())->toBeTrue();
    $this->get(route('documents.list-documents', 'Sent'))->assertStatus($canSendInternal ? 200 : 403);
})->with(['external only' => [false], 'external and internal' => [true]]);
