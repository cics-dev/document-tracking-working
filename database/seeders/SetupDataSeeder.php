<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SetupDataSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(__DIR__.'/data/setup.json'), true, 512, JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($data) {
            $this->upsert('roles', $data['roles']);
            $this->upsert('permissions', $data['permissions']);
            // Assign office heads after users exist to resolve circular references.
            $this->upsert('offices', array_map(fn ($office) => array_replace($office, [
                'head_id' => null, 'acting_head_id' => null,
            ]), $data['offices']));
            foreach ($data['users'] as $user) {
                if (DB::table('users')->where('id', $user['id'])->exists()) {
                    DB::table('users')->where('id', $user['id'])->update($user);
                } else {
                    DB::table('users')->insert($user + ['password' => Hash::make('password')]);
                }
            }
            $this->upsert('offices', $data['offices']);
            $this->upsert('user_profiles', $data['user_profiles']);
            $this->upsert('document_types', $data['document_types']);
            $this->upsert('permission_role', $data['permission_role'], ['permission_id', 'role_id']);
            $this->upsert('role_document_types', $data['role_document_types']);
            $this->upsert('workflow_conditions', $data['workflow_conditions']);
            $this->upsert('document_flow_stages', $data['document_flow_stages']);
            $this->upsert('document_generation_rules', $data['document_generation_rules']);
            $this->upsert('document_generation_rule_role', $data['document_generation_rule_role'], ['document_generation_rule_id', 'role_id']);
        });
    }

    private function upsert(string $table, array $rows, array $uniqueBy = ['id']): void
    {
        if ($rows === []) {
            return;
        }
        $updateColumns = array_values(array_diff(array_keys($rows[0]), $uniqueBy));
        if ($updateColumns === []) {
            DB::table($table)->insertOrIgnore($rows);
            return;
        }
        DB::table($table)->upsert($rows, $uniqueBy, $updateColumns);
    }
}
