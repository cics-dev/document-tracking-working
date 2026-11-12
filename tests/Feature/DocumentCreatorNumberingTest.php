<?php

use App\Livewire\Documents\CreateDocument;
use App\Models\{Document, DocumentType, Office, Permission, Role, User};
use Livewire\Livewire;

it('uses the creator office normally and the from office when the creator is its OIC', function (string $abbreviation, bool $manual, bool $isOic) {
    $role = Role::create(['role' => 'numbering-user', 'description' => 'Numbering user']);
    $role->permissions()->attach(Permission::where('key', 'send_documents')->firstOrFail());
    $creatorOffice = Office::create(['name' => 'Creator office', 'abbreviation' => 'SAO', 'office_type' => 'ADMIN', 'document_number_tag' => 'FINANCE']);
    $senderOffice = Office::create(['name' => 'Sender office', 'abbreviation' => 'OP', 'office_type' => 'ADMIN', 'document_number_tag' => 'ADMIN']);
    $creator = User::factory()->create(['office_id' => $creatorOffice->id, 'role_id' => $role->id]);
    $sender = User::factory()->create(['office_id' => $senderOffice->id]);
    $creatorOffice->update(['head_id' => $creator->id]);
    $senderOffice->update(['head_id' => $sender->id, 'acting_head_id' => $isOic ? $creator->id : null]);
    $type = DocumentType::create(['name' => 'Numbering '.$abbreviation, 'abbreviation' => $abbreviation, 'recipient_mode' => 'none', 'requires_signatories' => false, 'is_publicly_creatable' => true, 'document_level' => $abbreviation === 'INTRA' ? 'Intra' : 'Inter']);
    $prefix = ($isOic ? 'OP(ADMIN)-' : 'SAO(FINANCE)-').$abbreviation.'-';
    Document::create(['document_number' => $prefix.'4-'.date('Y'), 'from_id' => $senderOffice->id, 'document_type_id' => $type->id, 'created_by' => $creator->id, 'subject' => 'Earlier number', 'content' => 'Body', 'status' => 'Sent']);
    $draft = Document::create(['document_number' => null, 'from_id' => $senderOffice->id, 'document_type_id' => $type->id, 'created_by' => $creator->id, 'subject' => 'Numbering draft', 'content' => 'Body', 'status' => 'Draft']);
    $this->actingAs($creator);
    $component = Livewire::test(CreateDocument::class, ['draft_id' => $draft->id])
        ->assertSet('document_from_id', $senderOffice->id)
        ->set('is_manual_document_number', $manual);
    if ($manual) {
        $component->set('manual_document_sequence', '9')->set('manual_document_year', (string) date('Y'));
    }
    expect($component->instance()->manualDocumentNumberParts()[0]['value'])->toBe($prefix);
    $component->call('previewDocument')->assertDispatched('open-preview-tab');
    $preview = collect(session()->all())->first(fn ($value) => is_array($value) && ($value['action'] ?? null) === 'preview');
    expect($preview['documentNumber'])->toBe($prefix.($manual ? '9' : '_____').'-'.date('Y'));
    $component->call('submitDocument', 'send')->assertHasNoErrors();
    $saved = $draft->fresh();
    expect($saved->document_number)->toBe($prefix.($manual ? '9' : '5').'-'.date('Y'));
    expect($saved->created_by)->toBe($creator->id);
    expect($saved->from_id)->toBe($senderOffice->id);
})->with(['RLM', 'IOM', 'ECLR', 'INTRA', 'IL', 'SO'])->with([false, true])->with(['ordinary creator' => [false], 'OIC creator' => [true]]);
