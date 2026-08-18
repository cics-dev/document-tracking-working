<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::updateOrCreate(
            ['role' => 'admin'],
            ['description' => 'Administrator'],
        );
        User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'System Administrator',
            'password' => Hash::make('password'),
            'position' => 'Administrator',
            'role_id' => $adminRole->id,
            'office_id' => null,
        ]);
    }
}
