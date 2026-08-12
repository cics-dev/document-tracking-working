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
        ];

        foreach ($permissions as $key => $label) {
            DB::table('permissions')->updateOrInsert(['key' => $key], ['label' => $label, 'updated_at' => $now, 'created_at' => $now]);
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
        $ids = DB::table('permissions')->whereIn('key', ['receive_documents', 'send_documents', 'send_external_documents'])->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
