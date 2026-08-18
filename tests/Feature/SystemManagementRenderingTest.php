<?php

namespace Tests\Feature;

use App\Livewire\AccessRights\ManageAccessRights;
use App\Livewire\DocumentFlows\ManageDocumentFlows;
use App\Livewire\Documents\CreateDocument;
use App\Livewire\DocumentTypes\ManageDocumentTypes;
use App\Livewire\Offices\CreateOffice;
use App\Livewire\Roles\ManageRoles;
use App\Livewire\Users\CreateUser;
use App\Models\DocumentFlowStage;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SystemManagementRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_management_and_document_creation_forms_render_without_runtime_errors(): void
    {
        $role = Role::create(['role' => 'system-admin', 'description' => 'System Administrator']);
        foreach (['manage_document_flows', 'manage_roles', 'manage_access_rights', 'manage_offices', 'manage_users', 'send_documents'] as $key) {
            $role->permissions()->attach(Permission::firstOrCreate(['key' => $key], ['label' => str($key)->headline()]));
        }

        $office = Office::create(['name' => 'Test Office', 'abbreviation' => 'TEST', 'office_type' => 'ADMIN']);
        $user = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
        $office->update(['head_id' => $user->id]);
        $type = DocumentType::create([
            'name' => 'Dynamic Test Document', 'abbreviation' => 'DTD', 'recipient_mode' => 'office',
            'recipient_label' => 'To', 'recipient_office_id' => $office->id, 'document_level' => 'Inter',
            'show_thru' => true, 'show_carbon_copy' => true, 'requires_signatories' => true,
        ]);
        DB::table('role_document_types')->insert([
            'role_id' => $role->id, 'document_type_id' => $type->id, 'is_allowed' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DocumentFlowStage::create([
            'document_type_id' => $type->id, 'office_id' => $office->id, 'stage_type' => 'routing',
            'label' => 'Test review', 'description' => 'Runtime rendering check', 'sequence' => 10,
            'is_required' => false, 'is_selectable' => true,
        ]);

        $this->actingAs($user);

        foreach ([ManageAccessRights::class, ManageDocumentFlows::class, ManageDocumentTypes::class, ManageRoles::class, CreateOffice::class, CreateUser::class] as $component) {
            Livewire::test($component)->assertStatus(200);
        }

        Livewire::test(CreateDocument::class)
            ->set('document_type_id', (string) $type->id)
            ->call('handleUpdateDocumentType')
            ->call('addSignatory', ['role' => 'Reviewed by', 'office_id' => $office->id, 'locked' => false])
            ->assertSet('document_to_id', $office->id)
            ->assertSee('Runtime rendering check')
            ->assertStatus(200);
    }
}
