<?php

namespace Tests\Feature;

use App\Livewire\Offices\CreateOffice;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OfficeUserCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_office_can_be_created_with_a_new_head_account(): void
    {
        [$admin, $userRole] = $this->officeAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateOffice::class)
            ->set('name', 'Research Office')
            ->set('abbreviation', 'RO')
            ->set('office_type', 'ADMIN')
            ->set('office_head', '__new_user__')
            ->set('newUser.given_name', 'Maria')
            ->set('newUser.family_name', 'Santos')
            ->set('newUser.gender', 'female')
            ->set('newUser.email', 'maria@example.test')
            ->set('newUser.position', 'Director')
            ->set('newUser.role_id', (string) $userRole->id)
            ->call('saveOffice')
            ->assertHasNoErrors();

        $office = Office::where('abbreviation', 'RO')->firstOrFail();
        $head = User::where('email', 'maria@example.test')->firstOrFail();
        $this->assertSame($office->id, $head->office_id);
        $this->assertSame($head->id, $office->head_id);
        $this->assertDatabaseHas('user_profiles', ['user_id' => $head->id, 'given_name' => 'Maria', 'family_name' => 'Santos']);
    }

    public function test_office_can_be_edited_with_a_new_oic_account(): void
    {
        [$admin, $userRole] = $this->officeAdmin();
        $office = Office::create(['name' => 'Records', 'abbreviation' => 'REC', 'office_type' => 'ADMIN']);
        $this->actingAs($admin);

        Livewire::test(CreateOffice::class, ['id' => $office->id])
            ->set('acting_head', '__new_user__')
            ->set('newUser.given_name', 'Jose')
            ->set('newUser.family_name', 'Reyes')
            ->set('newUser.gender', 'male')
            ->set('newUser.email', 'jose@example.test')
            ->set('newUser.position', 'Officer-in-Charge')
            ->set('newUser.role_id', (string) $userRole->id)
            ->call('saveOffice')
            ->assertHasNoErrors();

        $oic = User::where('email', 'jose@example.test')->firstOrFail();
        $this->assertSame($oic->id, $office->fresh()->acting_head_id);
        $this->assertSame($office->id, $oic->office_id);
    }

    private function officeAdmin(): array
    {
        $adminRole = Role::create(['role' => 'admin', 'description' => 'Administrator']);
        $userRole = Role::create(['role' => 'officer', 'description' => 'Officer']);
        $permission = Permission::firstOrCreate(['key' => 'manage_offices'], ['label' => 'Manage offices']);
        $adminRole->permissions()->attach($permission);

        return [User::factory()->create(['role_id' => $adminRole->id]), $userRole];
    }
}
