<?php

namespace Tests\Feature;

use App\Livewire\Roles\ManageRoles;
use App\Models\Permission;
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

    private function accessAdmin(): User
    {
        $role = Role::create(['role' => 'admin', 'description' => 'Administrator']);
        $permission = Permission::create(['key' => 'manage_access_rights', 'label' => 'Manage access rights']);
        $role->permissions()->attach($permission);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
