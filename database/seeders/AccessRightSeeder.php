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
            'manage_offices' => 'Manage Offices',
            'manage_users' => 'Manage Users',
            'manage_roles' => 'Manage Roles',
            'manage_access_rights' => 'Manage Access Rights',
            'manage_document_flows' => 'Manage Document Flows',
            'receive_documents' => 'Receive Documents',
            'send_documents' => 'Send Documents',
            'view_all_documents' => 'View All Documents',
            'receive_external_documents' => 'Receive External',
            'send_external_documents' => 'Send External',
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
