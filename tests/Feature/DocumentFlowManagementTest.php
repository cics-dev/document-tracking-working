<?php

namespace Tests\Feature;

use App\Livewire\DocumentFlows\ManageDocumentFlows;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentFlowManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_signatory_stage_label_is_automatically_recommending_approval(): void
    {
        $role = Role::create(['role' => 'admin', 'description' => 'Administrator']);
        $permission = Permission::create(['key' => 'manage_document_flows', 'label' => 'Manage document flows']);
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
        $permission = Permission::create(['key' => 'manage_document_flows', 'label' => 'Manage document flows']);
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
}
