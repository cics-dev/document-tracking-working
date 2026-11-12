<?php

use App\Models\{Document, DocumentGenerationRule, DocumentType, Office, Permission, Role, User};
use App\Livewire\Documents\CreateDocument;
use App\Services\DocumentGenerationService;
use Livewire\Livewire;

it('uses the creating users office and document tag for generated IOM previews and numbers', function (string $tag, bool $crossOfficeOic) {
    $role = Role::create(['role' => 'iom-generator', 'description' => 'SAO']);
    $role->permissions()->attach(Permission::where('key', 'send_documents')->firstOrFail());
    $office = Office::create(['name' => 'SAO '.$tag, 'abbreviation' => 'SAO', 'document_number_tag' => $tag, 'office_type' => 'ADMIN']);
    $creator = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
    $office->update(['head_id' => $creator->id]);
    if ($crossOfficeOic) {
        $home = Office::create(['name' => 'OIC home', 'abbreviation' => 'HOME', 'office_type' => 'ADMIN']);
        $oic = User::factory()->create(['role_id' => $role->id, 'office_id' => $home->id]);
        $head = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
        $office->update(['head_id' => $head->id, 'acting_head_id' => $oic->id]);
    }
    $op = Office::create(['name' => 'President', 'abbreviation' => 'OP', 'document_number_tag' => 'ADMIN', 'office_type' => 'ADMIN']);
    $sourceType = DocumentType::create(['name' => 'Request Letter Memorandum', 'abbreviation' => 'RLM']);
    $targetType = DocumentType::create(['name' => 'Inter Office Memorandum', 'abbreviation' => 'IOM', 'recipient_mode' => 'none', 'requires_signatories' => false, 'is_publicly_creatable' => true]);
    $source = Document::create(['document_number' => 'SOURCE-RLM-1', 'from_id' => $op->id, 'to_id' => $op->id, 'document_type_id' => $sourceType->id, 'subject' => 'Source RLM', 'content' => 'Body', 'created_by' => $creator->id, 'status' => 'Approved']);
    $rule = DocumentGenerationRule::create(['source_context' => 'internal', 'source_document_type_id' => $sourceType->id, 'target_document_type_id' => $targetType->id, 'button_label' => 'Generate IOM', 'required_status' => 'Approved', 'requires_assigned_office' => false]);
    $rule->roles()->attach($role->id);
    $this->actingAs($creator);
    $data = app(DocumentGenerationService::class)->redirectData($rule, $source, $creator);
    expect($data['from'])->toBe($office->id);
    session()->flash('redirect_data', $data);
    $component = Livewire::test(CreateDocument::class)->assertSet('document_from_id', $office->id)
        ->set('content', 'IOM content')->call('previewDocument')->assertDispatched('open-preview-tab');
    $preview = collect(session()->all())->first(fn ($value) => is_array($value) && ($value['action'] ?? null) === 'preview');
    expect($preview['documentNumber'])->toBe('SAO('.$tag.')-IOM-_____-'.date('Y'));
    $component->call('submitDocument', 'send')->assertHasNoErrors();
    $iom = Document::where('document_type_id', $targetType->id)->firstOrFail();
    expect($iom->created_by)->toBe($creator->id);
    expect($iom->from_id)->toBe($office->id);
    expect($iom->document_number)->toBe('SAO('.$tag.')-IOM-1-'.date('Y'));
})->with([['ADMIN', false], ['FINANCE', false], ['ADMIN', true]]);
