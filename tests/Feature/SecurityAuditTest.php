<?php

use App\Livewire\Documents\CreateDocument;
use App\Livewire\Documents\ListDocuments;
use App\Models\{Document, DocumentType, ExternalDocument, Office, Permission, Role, User};
use App\Services\UserAccountService;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $role = Role::create(['role' => 'audit-sender', 'description' => 'Audit sender']);
    foreach (['send_documents', 'receive_external_documents'] as $key) {
        $role->permissions()->attach(Permission::firstOrCreate(['key' => $key], ['label' => $key]));
    }
    $this->office = Office::create(['name' => 'Audit office', 'abbreviation' => 'AUD', 'office_type' => 'ADMIN']);
    $this->actor = User::factory()->create(['role_id' => $role->id, 'office_id' => $this->office->id]);
    $this->office->update(['head_id' => $this->actor->id]);
    $this->otherOffice = Office::create(['name' => 'Other office', 'abbreviation' => 'OTH', 'office_type' => 'ADMIN']);
    $this->other = User::factory()->create(['office_id' => $this->otherOffice->id]);
    $this->otherOffice->update(['head_id' => $this->other->id]);
    $this->type = DocumentType::create(['name' => 'Audit type', 'abbreviation' => 'AUD', 'recipient_mode' => 'none', 'is_publicly_creatable' => true, 'requires_signatories' => false]);
    $this->privateDocument = Document::create(['document_number' => 'PRIVATE-AUD-1', 'document_type_id' => $this->type->id, 'from_id' => $this->otherOffice->id, 'created_by' => $this->other->id, 'subject' => 'CONFIDENTIAL AUDIT SUBJECT', 'content' => 'Private', 'status' => 'Draft', 'document_level' => 'Inter']);
    $this->actingAs($this->actor);
});

// Audit probes deliberately assert the vulnerable behavior to confirm reproducibility.
it('reproduces changing a permitted list into the global list', function () {
    expect($this->actor->hasAccess('view_all_documents'))->toBeFalse();
    Livewire::test(ListDocuments::class, ['mode' => 'Sent'])
        ->assertDontSee('CONFIDENTIAL AUDIT SUBJECT')
        ->set('mode', 'all')->assertSee('CONFIDENTIAL AUDIT SUBJECT');
});

it('reproduces overwriting another users draft through mutable source id', function () {
    Livewire::test(CreateDocument::class)
        ->set('document_type_id', (string) $this->type->id)
        ->set('original_document_id', $this->privateDocument->id)
        ->set('subject', 'OVERWRITTEN BY OTHER USER')
        ->call('submitDocument', 'draft')->assertHasNoErrors();
    expect($this->privateDocument->fresh()->subject)->toBe('OVERWRITTEN BY OTHER USER');
    expect($this->privateDocument->fresh()->created_by)->toBe($this->actor->id);
});

it('reproduces opening an external document outside the users office', function () {
    $external = ExternalDocument::create(['document_number' => 'EXT-AUD-1', 'from' => 'Outside', 'to_id' => $this->otherOffice->id, 'subject' => 'EXTERNAL PRIVATE SUBJECT', 'received_date' => now(), 'file_url' => 'attachments/private.pdf', 'file_type' => 'pdf']);
    $this->get(route('documents.list-external-documents'))->assertOk()->assertDontSee('EXTERNAL PRIVATE SUBJECT');
    Livewire::test(\App\Livewire\Documents\ViewExternalDocument::class, ['id' => $external->id])->assertSet('document.id', $external->id);
    \Illuminate\Support\Facades\Storage::fake('public');
    \Illuminate\Support\Facades\Storage::disk('public')->put($external->file_url, '%PDF-1.4 audit');
    $this->get(route('documents.external-document-preview', $external))->assertOk();
});

it('reproduces predictable passwords on administrator created accounts', function () {
    $user = app(UserAccountService::class)->save(['given_name' => 'Audit', 'family_name' => 'Account', 'email' => 'audit-account@example.test', 'office_id' => $this->office->id, 'role_id' => $this->actor->role_id, 'position' => 'Staff', 'gender' => 'female']);
    expect(Hash::check('password', $user->password))->toBeTrue();
});

it('reproduces guest access to the paid chat endpoint', function () {
    auth()->logout();
    $this->postJson(route('chat.send'), [])->assertUnprocessable()->assertJsonValidationErrors('message');
    $route = app('router')->getRoutes()->getByName('chat.send');
    expect(collect($route->gatherMiddleware())->filter(fn ($m) => str_starts_with($m, 'throttle') || $m === 'auth'))->toBeEmpty();
});
