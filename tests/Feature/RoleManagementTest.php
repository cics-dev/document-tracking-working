<?php

namespace Tests\Feature;

use App\Livewire\Roles\ManageRoles;
use App\Livewire\AccessRights\ManageAccessRights;
use App\Models\Permission;
use App\Models\DocumentType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_admin_can_create_update_and_delete_an_unassigned_role(): void
    {
        $this->actingAs($this->accessAdmin());

        Livewire::test(ManageRoles::class)
            ->set('role', 'records-reviewer')
            ->set('description', 'Records Reviewer')
            ->call('save')
            ->assertHasNoErrors();

        $role = Role::where('role', 'records-reviewer')->firstOrFail();

        Livewire::test(ManageRoles::class)
            ->call('edit', $role->id)
            ->set('description', 'Senior Records Reviewer')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'description' => 'Senior Records Reviewer']);

        Livewire::test(ManageRoles::class)->call('delete', $role->id)->assertHasNoErrors('delete');
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_assigned_role_cannot_be_deleted(): void
    {
        $this->actingAs($this->accessAdmin());
        $assigned = Role::create(['role' => 'assigned', 'description' => 'Assigned Role']);
        User::factory()->create(['role_id' => $assigned->id]);

        Livewire::test(ManageRoles::class)
            ->call('delete', $assigned->id)
            ->assertHasErrors('delete');

        $this->assertDatabaseHas('roles', ['id' => $assigned->id]);
    }

    public function test_access_rights_can_be_updated_for_one_role_without_changing_another(): void
    {
        $admin = $this->accessAdmin();
        $this->actingAs($admin);
        $first = Role::create(['role' => 'first', 'description' => 'First Role']);
        $second = Role::create(['role' => 'second', 'description' => 'Second Role']);
        $receive = Permission::where('key', 'receive_documents')->firstOrFail();
        $send = Permission::where('key', 'send_documents')->firstOrFail();
        $second->permissions()->attach($send);

        Livewire::test(ManageAccessRights::class)
            ->set("rights.{$first->id}", [(string) $receive->id])
            ->set("rights.{$second->id}", [])
            ->call('saveRole', $first->id)
            ->assertHasNoErrors();

        $this->assertTrue($first->fresh()->permissions->contains($receive));
        $this->assertTrue($second->fresh()->permissions->contains($send));
    }

    public function test_new_role_is_automatically_shown_on_access_rights_page(): void
    {
        $this->actingAs($this->accessAdmin());
        $newRole = Role::create(['role' => 'new-role', 'description' => 'New Role']);

        Livewire::test(ManageAccessRights::class)
            ->assertSee('New Role')
            ->assertSee("Update {$newRole->description}");
    }

    public function test_public_document_type_is_shown_as_enabled_for_every_role(): void
    {
        $this->actingAs($this->accessAdmin());
        $role = Role::create(['role' => 'public-type-user', 'description' => 'Public Type User']);
        $type = DocumentType::create([
            'name' => 'Public Office Memorandum',
            'abbreviation' => 'POM',
            'is_publicly_creatable' => true,
        ]);

        Livewire::test(ManageAccessRights::class)
            ->assertSee('Public Office Memorandum')
            ->assertSee('All users')
            ->assertSeeHtml('data-public-document-type="'.$type->id.'"')
            ->assertSeeHtml('checked disabled');

        $this->assertDatabaseMissing('role_document_types', [
            'role_id' => $role->id,
            'document_type_id' => $type->id,
        ]);
    }

    public function test_role_can_be_created_with_access_rights_and_document_types(): void
    {
        $this->actingAs($this->accessAdmin());
        $permission = Permission::where('key', 'receive_documents')->firstOrFail();
        $type = DocumentType::create(['name' => 'Test Memorandum', 'abbreviation' => 'TM']);

        Livewire::test(ManageRoles::class)
            ->set('role', 'receiver')
            ->set('description', 'Document Receiver')
            ->set('rights', [(string) $permission->id])
            ->set('documentTypes', [(string) $type->id])
            ->call('save')
            ->assertHasNoErrors();

        $role = Role::where('role', 'receiver')->firstOrFail();
        $this->assertTrue($role->permissions()->where('key', 'receive_documents')->exists());
        $this->assertDatabaseHas('role_document_types', [
            'role_id' => $role->id,
            'document_type_id' => $type->id,
            'is_allowed' => true,
        ]);
    }

    public function test_document_lists_require_the_matching_access_right(): void
    {
        $role = Role::create(['role' => 'restricted', 'description' => 'Restricted']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($user);

        $this->get(route('documents.list-documents', 'received'))->assertForbidden();
        $this->get(route('documents.list-documents', 'Sent'))->assertForbidden();
        $this->get(route('documents.list-documents', 'all'))->assertForbidden();
        $this->get(route('documents.list-external-documents'))->assertForbidden();
        $this->get(route('documents.receive-external-document'))->assertForbidden();
    }

    private function accessAdmin(): User
    {
        $role = Role::create(['role' => 'admin', 'description' => 'Administrator']);
        $permissions = Permission::whereIn('key', ['manage_access_rights', 'manage_roles'])->pluck('id');
        $role->permissions()->attach($permissions);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
