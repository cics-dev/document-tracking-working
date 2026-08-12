<?php

namespace Tests\Feature;

use App\Livewire\DocumentFlows\ManageDocumentFlows;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentFlowManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_signatory_stage_label_is_automatically_recommending_approval(): void
    {
        $role = Role::create(['role' => 'admin', 'description' => 'Administrator']);
        $permission = Permission::firstOrCreate(['key' => 'manage_document_flows'], ['label' => 'Manage Document Flows']);
        $role->permissions()->attach($permission);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));

        $type = DocumentType::create(['name' => 'Request Letter', 'abbreviation' => 'RLM']);
        $office = Office::create(['name' => 'Recommending Office', 'abbreviation' => 'RO', 'office_type' => 'ADMIN']);

        Livewire::test(ManageDocumentFlows::class)
            ->set('documentTypeId', (string) $type->id)
            ->set('officeId', (string) $office->id)
            ->set('stageType', 'signatory')
            ->set('label', 'Recommending Approval')
            ->set('isRequired', false)
            ->set('isSelectable', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('document_flow_stages', [
            'document_type_id' => $type->id,
            'office_id' => $office->id,
            'stage_type' => 'signatory',
            'label' => 'Recommending Approval',
        ]);
    }

    public function test_signatory_stage_can_be_labeled_approved_by(): void
    {
        $role = Role::create(['role' => 'admin', 'description' => 'Administrator']);
        $permission = Permission::firstOrCreate(['key' => 'manage_document_flows'], ['label' => 'Manage Document Flows']);
        $role->permissions()->attach($permission);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        $type = DocumentType::create(['name' => 'Special Order', 'abbreviation' => 'SO']);
        $office = Office::create(['name' => 'President', 'abbreviation' => 'OP', 'office_type' => 'ADMIN']);

        Livewire::test(ManageDocumentFlows::class)
            ->set('documentTypeId', (string) $type->id)
            ->set('officeId', (string) $office->id)
            ->set('stageType', 'signatory')
            ->set('label', 'Approved by')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('document_flow_stages', [
            'office_id' => $office->id, 'stage_type' => 'signatory', 'label' => 'Approved by',
        ]);
    }

    public function test_boolean_no_expected_value_is_saved_as_zero_instead_of_null(): void
    {
        $role = Role::create(['role' => 'flow-admin', 'description' => 'Flow Administrator']);
        $role->permissions()->attach(Permission::where('key', 'manage_document_flows')->firstOrFail());
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        $type = DocumentType::create(['name' => 'Conditional Letter', 'abbreviation' => 'CL']);
        $office = Office::create(['name' => 'Conditional Office', 'abbreviation' => 'CO', 'office_type' => 'ADMIN']);
        $condition = WorkflowCondition::create(['key' => 'has_funds', 'label' => 'Has funds?', 'input_type' => 'boolean']);

        Livewire::test(ManageDocumentFlows::class)
            ->set('documentTypeId', (string) $type->id)->set('officeId', (string) $office->id)
            ->set('stageType', 'routing')->set('label', 'No-funds review')
            ->set('workflowConditionId', (string) $condition->id)
            ->set('conditionOperator', 'equals')->set('conditionValue', '0')
            ->call('save')->assertHasNoErrors();

        $this->assertDatabaseHas('document_flow_stages', [
            'office_id' => $office->id, 'workflow_condition_id' => $condition->id,
            'condition_operator' => 'equals', 'condition_value' => '0',
        ]);
    }
}
