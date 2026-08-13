<?php

namespace Tests\Feature;

use App\Livewire\Offices\CreateOffice;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OfficeInlineUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_office_can_be_created_with_separate_new_head_and_oic_accounts(): void
    {
        $adminRole = Role::create(['role' => 'admin', 'description' => 'Administrator']);
        $staffRole = Role::create(['role' => 'head', 'description' => 'Office Head']);
        $permission = Permission::firstOrCreate(['key' => 'manage_offices'], ['label' => 'Manage offices']);
        $adminRole->permissions()->attach($permission);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->actingAs($admin);

        Livewire::test(CreateOffice::class)
            ->set('name', 'New Office')
            ->set('abbreviation', 'NEW')
            ->set('office_type', 'ADMIN')
            ->set('office_head', '__new_user__')
            ->set('acting_head', '__new_user__')
            ->set('newHead', $this->account('Helen', 'Head', 'head@example.com', $staffRole->id))
            ->set('newOic', $this->account('Oscar', 'Oic', 'oic@example.com', $staffRole->id))
            ->call('saveOffice')
            ->assertHasNoErrors();

        $office = \App\Models\Office::where('abbreviation', 'NEW')->firstOrFail();
        $this->assertSame('head@example.com', $office->head->email);
        $this->assertSame('oic@example.com', $office->actingHead->email);
        $this->assertNotSame($office->head_id, $office->acting_head_id);
    }

    private function account(string $given, string $family, string $email, int $roleId): array
    {
        return [
            'family_name' => $family, 'given_name' => $given, 'middle_name' => '', 'middle_initial' => '',
            'suffix' => '', 'honorifics' => '', 'titles' => '', 'gender' => 'Other', 'email' => $email,
            'office_id' => '', 'position' => 'Officer', 'role_id' => (string) $roleId,
            'is_head' => false, 'signature' => null,
        ];
    }
}
