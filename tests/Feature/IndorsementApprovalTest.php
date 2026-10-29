<?php

use App\Livewire\DocumentFlows\ManageDocumentFlows;
use App\Livewire\Documents\CreateDocument;
use App\Models\Document;
use App\Models\DocumentFlowStage;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->staffRole = Role::create(['role' => 'flow-staff', 'description' => 'Staff']);
    $this->vpRole = Role::create(['role' => 'vice-president', 'description' => 'Vice President']);
    $this->presRole = Role::create(['role' => 'president', 'description' => 'President']);
    foreach ([$this->staffRole, $this->vpRole, $this->presRole] as $role) {
        $role->permissions()->attach(Permission::where('key', 'send_documents')->firstOrFail());
    }
    $office = function ($name, $role) {
        $office = Office::create(['name' => $name, 'abbreviation' => str_replace(' ', '', $name), 'office_type' => 'ADMIN']);
        $head = User::factory()->create(['office_id' => $office->id, 'role_id' => $role->id]);
        $office->update(['head_id' => $head->id]);

        return $office->fresh();
    };
    $this->sender = $office('College', $this->staffRole);
    $this->vpA = $office('VP Academic', $this->vpRole);
    $this->vpB = $office('VP Finance', $this->vpRole);
    $this->president = $office('President', $this->presRole);
    $this->type = DocumentType::create([
        'name' => 'Indorsement Letter', 'abbreviation' => 'IL', 'recipient_mode' => 'none',
        'requires_signatories' => true, 'is_publicly_creatable' => true, 'allow_oic_signature' => true,
    ]);
    $this->presStage = DocumentFlowStage::create([
        'document_type_id' => $this->type->id, 'office_id' => $this->president->id,
        'stage_type' => 'signatory', 'label' => 'Approved by', 'sequence' => 1,
        'is_required' => true, 'is_selectable' => false, 'sender_role_id' => $this->vpRole->id,
        'sender_role_operator' => 'equals',
    ]);
    foreach (['vpA', 'vpB'] as $name) {
        $this->{$name.'Stage'} = DocumentFlowStage::create([
            'document_type_id' => $this->type->id, 'office_id' => $this->{$name}->id,
            'stage_type' => 'signatory', 'label' => 'Recommending Approval', 'sequence' => 2,
            'is_required' => true, 'sender_role_id' => $this->vpRole->id,
            'sender_role_operator' => 'not_equals', 'selection_group' => 'VP approval',
        ]);
    }
    $this->groupKey = 'group_'.md5('VP approval');
    $this->choiceField = 'flowGroupSelections.'.$this->groupKey;
    $this->actingAs($this->sender->head);
    $this->compose = fn () => Livewire::test(CreateDocument::class)
        ->set('document_type_id', (string) $this->type->id)->call('handleUpdateDocumentType')
        ->set('subject', 'Configured routing')->set('content', '<p>Body</p>');
});

it('requires one of the configured VP choices for non-VP senders', function () {
    ($this->compose)()->call('submitDocument', 'send')->assertHasErrors([$this->choiceField]);
    expect(Document::count())->toBe(0);
});

it('sends to exactly the selected configured VP instead of the President', function () {
    ($this->compose)()->set($this->choiceField, (string) $this->vpBStage->id)
        ->call('submitDocument', 'send')->assertHasNoErrors();
    $steps = Document::firstOrFail()->steps;
    expect($steps)->toHaveCount(1);
    expect($steps->first()->office_id)->toBe($this->vpB->id);
});

it('requires the configured President stage for staff preparing a document in a VP office', function () {
    $staff = User::factory()->create(['role_id' => $this->staffRole->id, 'office_id' => $this->vpA->id]);
    $this->actingAs($staff);
    ($this->compose)()->assertSet('signatories.0.office_id', $this->president->id)
        ->assertSet('signatories.0.locked', true)
        ->call('submitDocument', 'send')->assertHasNoErrors();
    expect(Document::firstOrFail()->steps->pluck('office_id')->all())->toBe([$this->president->id]);
});

it('uses the office head role even when the actor has a VP role in another office', function () {
    $actor = User::factory()->create(['role_id' => $this->vpRole->id, 'office_id' => $this->sender->id]);
    $this->actingAs($actor);
    ($this->compose)()->call('submitDocument', 'send')->assertHasErrors([$this->choiceField]);
});

it('rejects a choice from a different stage or multiple values', function ($invalid) {
    $value = $invalid === 'multiple' ? [(string) $this->vpAStage->id, (string) $this->vpBStage->id] : (string) $this->presStage->id;
    ($this->compose)()->set($this->choiceField, $value)
        ->call('submitDocument', 'send')->assertHasErrors([$this->choiceField]);
    expect(Document::count())->toBe(0);
})->with(['other-stage', 'multiple']);

it('rejects using a generic signatory to add a second grouped office', function () {
    ($this->compose)()->set($this->choiceField, (string) $this->vpAStage->id)
        ->set('signatories', [['role' => 'Recommending Approval', 'office_id' => $this->vpB->id]])
        ->call('submitDocument', 'send')->assertHasErrors(['signatories']);
    expect(Document::count())->toBe(0);
});

it('does not impose any built-in IL approval when the rules are removed', function () {
    DocumentFlowStage::query()->delete();
    $this->type->update(['requires_signatories' => false]);
    ($this->compose)()->call('submitDocument', 'send')->assertHasNoErrors();
    expect(Document::firstOrFail()->steps)->toHaveCount(0);
});

it('supports the same configured rules on other document types', function () {
    $this->type->update(['abbreviation' => 'CUSTOM']);
    ($this->compose)()->set($this->choiceField, (string) $this->vpAStage->id)
        ->call('submitDocument', 'send')->assertHasNoErrors();
    expect(Document::firstOrFail()->steps->pluck('office_id')->all())->toBe([$this->vpA->id]);
});

it('honors OIC assignment at the selected office', function () {
    $oic = User::factory()->create(['role_id' => $this->staffRole->id, 'office_id' => $this->vpB->id]);
    $this->vpB->update(['acting_head_id' => $oic->id]);
    ($this->compose)()->set($this->choiceField, (string) $this->vpBStage->id)
        ->call('submitDocument', 'send')->assertHasNoErrors();
    expect(Document::firstOrFail()->steps->first()->user_id)->toBe($oic->id);
});

it('allows an incomplete draft and clears choices when the sending office changes', function () {
    ($this->compose)()->set($this->choiceField, (string) $this->vpAStage->id)
        ->set('document_from_id', $this->vpA->id)->assertSet('flowGroupSelections', [])
        ->assertSet('signatories.0.office_id', $this->president->id)
        ->call('submitDocument', 'draft')->assertHasNoErrors();
    expect(Document::firstOrFail()->status)->toBe('Draft');
});

it('uses only configured offices rather than including every VP automatically', function () {
    $this->vpBStage->delete();
    ($this->compose)()->set($this->choiceField, (string) $this->vpBStage->id)
        ->call('submitDocument', 'send')->assertHasErrors([$this->choiceField]);
});

it('shows the configured choice in the preview', function () {
    ($this->compose)()->set($this->choiceField, (string) $this->vpAStage->id)
        ->call('previewDocument')->assertHasNoErrors()
        ->assertDispatched('open-preview-tab', function ($event, $params) {
            $signatories = json_decode(session(parse_url($params[0]['url'], PHP_URL_QUERY))['signatories'], true);
            expect($signatories)->toHaveCount(1);
            expect($signatories[0]['user_name'])->toBe($this->vpA->head->name);

            return true;
        });
});

it('lets an authorized administrator create edit and delete sender rules and groups in Flows', function () {
    $this->staffRole->permissions()->attach(Permission::where('key', 'manage_document_flows')->firstOrFail());
    $component = Livewire::test(ManageDocumentFlows::class)
        ->set('documentTypeId', (string) $this->type->id)->set('officeId', (string) $this->vpA->id)
        ->set('stageType', 'signatory')->set('label', 'Recommending Approval')
        ->set('senderRoleId', (string) $this->vpRole->id)->set('senderRoleOperator', 'not_equals')
        ->set('selectionGroup', 'Choose a VP')->set('isRequired', true)->call('save')->assertHasNoErrors();
    $stage = DocumentFlowStage::where('selection_group', 'Choose a VP')->firstOrFail();
    expect($stage->sender_role_id)->toBe($this->vpRole->id);
    $component->call('edit', $stage->id)->assertSet('selectionGroup', 'Choose a VP')
        ->set('senderRoleOperator', 'equals')->call('save')->assertHasNoErrors();
    expect($stage->fresh()->sender_role_operator)->toBe('equals');
    $component->call('delete', $stage->id);
    expect($stage->fresh())->toBeNull();
});

it('denies flow configuration to users without management permission', function () {
    Livewire::test(ManageDocumentFlows::class)->assertForbidden();
});


it('initializes the nested choice binding before the dropdown is rendered', function () {
    ($this->compose)()->assertSet($this->choiceField, '')
        ->set($this->choiceField, (string) $this->vpAStage->id)
        ->set('conditionValues.unrelated', true)
        ->assertSet($this->choiceField, (string) $this->vpAStage->id)
        ->set('document_from_id', $this->vpA->id)->assertSet('flowGroupSelections', [])
        ->set('document_from_id', $this->sender->id)->assertSet($this->choiceField, '')
        ->set($this->choiceField, (string) $this->vpBStage->id)
        ->call('submitDocument', 'send')->assertHasNoErrors();

    expect(Document::firstOrFail()->steps->pluck('office_id')->all())->toBe([$this->vpB->id]);
});
