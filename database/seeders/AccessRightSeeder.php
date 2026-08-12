<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AccessRightSeeder extends Seeder
{
    public function run(): void
    {
        $rights = [
            'manage_offices' => 'Manage offices and OIC assignments',
            'manage_users' => 'Manage users',
            'manage_access_rights' => 'Manage access rights',
            'manage_document_flows' => 'Manage document flows',
            'view_all_documents' => 'View all documents',
            'receive_external_documents' => 'Receive external documents',
        ];

        foreach ($rights as $key => $label) {
            Permission::updateOrCreate(['key' => $key], ['label' => $label]);
        }

        $admin = Role::where('role', 'admin')->first();
        $admin?->permissions()->sync(Permission::pluck('id'));

        Role::where('role', 'staff')->first()?->permissions()->sync(
            Permission::whereIn('key', ['receive_external_documents'])->pluck('id')
        );
    }
}
