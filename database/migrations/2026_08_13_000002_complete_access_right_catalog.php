<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $permissions = [
            'receive_documents' => 'Receive Documents',
            'send_documents' => 'Send Documents',
            'view_all_documents' => 'View All Documents',
            'receive_external_documents' => 'Receive External',
            'send_external_documents' => 'Send External',
            'manage_offices' => 'Manage Offices',
            'manage_users' => 'Manage Users',
            'manage_roles' => 'Manage Roles',
            'manage_access_rights' => 'Manage Access Rights',
            'manage_document_flows' => 'Manage Document Flows',
        ];

        foreach ($permissions as $key => $label) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $key],
                ['label' => $label, 'created_at' => $now, 'updated_at' => $now],
            );
        }

        $manageAccessId = DB::table('permissions')->where('key', 'manage_access_rights')->value('id');
        $manageRolesId = DB::table('permissions')->where('key', 'manage_roles')->value('id');
        if ($manageAccessId && $manageRolesId) {
            foreach (DB::table('permission_role')->where('permission_id', $manageAccessId)->pluck('role_id') as $roleId) {
                DB::table('permission_role')->insertOrIgnore(['permission_id' => $manageRolesId, 'role_id' => $roleId]);
            }
        }

        $adminRoleId = DB::table('roles')->where('role', 'admin')->value('id');
        if ($adminRoleId) {
            foreach (DB::table('permissions')->whereIn('key', array_keys($permissions))->pluck('id') as $permissionId) {
                DB::table('permission_role')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $adminRoleId]);
            }
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'manage_roles')->value('id');
        if ($permissionId) {
            DB::table('permission_role')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};
