<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SetupDataSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            $this->upsert('roles', [
                ['id' => 1, 'role' => 'admin', 'description' => 'Administrator'],
                ['id' => 2, 'role' => 'head', 'description' => 'head'],
                ['id' => 3, 'role' => 'president', 'description' => 'University President'],
                ['id' => 4, 'role' => 'vice-president', 'description' => 'Vice President'],
                ['id' => 5, 'role' => 'cao', 'description' => 'Chief Administrative Officer'],
                ['id' => 6, 'role' => 'sao', 'description' => 'Supervising Administrative Officer'],
                ['id' => 7, 'role' => 'records', 'description' => 'Records Officer'],
            ]);

            $this->upsert('permissions', [
                ['id' => 1, 'key' => 'manage_offices', 'label' => 'Manage Offices'],
                ['id' => 2, 'key' => 'manage_users', 'label' => 'Manage Users'],
                ['id' => 3, 'key' => 'manage_roles', 'label' => 'Manage Roles'],
                ['id' => 4, 'key' => 'manage_access_rights', 'label' => 'Manage Access Rights'],
                ['id' => 5, 'key' => 'manage_document_flows', 'label' => 'Manage Document Flows'],
                ['id' => 6, 'key' => 'receive_documents', 'label' => 'Receive Documents'],
                ['id' => 7, 'key' => 'send_documents', 'label' => 'Send Documents'],
                ['id' => 8, 'key' => 'view_all_documents', 'label' => 'View All Documents'],
                ['id' => 9, 'key' => 'receive_external_documents', 'label' => 'Receive External'],
                ['id' => 10, 'key' => 'send_external_documents', 'label' => 'Send External'],
            ]);

            $documentTypes = [
                ['id' => 1, 'name' => 'Request Letter Memorandum', 'abbreviation' => 'RLM', 'chip_color' => '#dbeafe', 'recipient_mode' => 'office', 'recipient_label' => 'For', 'recipient_office_id' => 1, 'document_level' => 'Inter', 'number_prefix' => '{office_with_type}-{type}', 'show_thru' => 1, 'show_carbon_copy' => 0, 'allow_attachments' => 1, 'requires_signatories' => 1, 'is_publicly_creatable' => 1, 'content_template' => null, 'print_layout' => 'memorandum', 'sender_signature_policy' => 'always', 'approver_display_mode' => 'action_box', 'allow_oic_signature' => 1],
                ['id' => 2, 'name' => 'Inter Office Memorandum', 'abbreviation' => 'IOM', 'chip_color' => '#d1fae5', 'recipient_mode' => 'none', 'recipient_label' => 'To', 'recipient_office_id' => null, 'document_level' => 'Inter', 'number_prefix' => null, 'show_thru' => 0, 'show_carbon_copy' => 1, 'allow_attachments' => 1, 'requires_signatories' => 1, 'is_publicly_creatable' => 0, 'content_template' => null, 'print_layout' => 'memorandum', 'sender_signature_policy' => 'approved', 'approver_display_mode' => 'hidden', 'allow_oic_signature' => 1],
                ['id' => 3, 'name' => 'External Communication Letter Response', 'abbreviation' => 'ECLR', 'chip_color' => '#ede9fe', 'recipient_mode' => 'text', 'recipient_label' => 'To', 'recipient_office_id' => null, 'document_level' => 'Inter', 'number_prefix' => null, 'show_thru' => 0, 'show_carbon_copy' => 0, 'allow_attachments' => 1, 'requires_signatories' => 1, 'is_publicly_creatable' => 1, 'content_template' => null, 'print_layout' => 'letter', 'sender_signature_policy' => 'approved', 'approver_display_mode' => 'signature_only', 'allow_oic_signature' => 0],
                ['id' => 4, 'name' => 'Intra Office Memorandum', 'abbreviation' => 'INTRA', 'chip_color' => '#fef3c7', 'recipient_mode' => 'text', 'recipient_label' => 'To', 'recipient_office_id' => null, 'document_level' => 'Intra', 'number_prefix' => null, 'show_thru' => 0, 'show_carbon_copy' => 0, 'allow_attachments' => 1, 'requires_signatories' => 1, 'is_publicly_creatable' => 1, 'content_template' => null, 'print_layout' => 'memorandum', 'sender_signature_policy' => 'always', 'approver_display_mode' => 'hidden', 'allow_oic_signature' => 1],
                ['id' => 5, 'name' => 'Indorsement Letter', 'abbreviation' => 'IL', 'chip_color' => '#ffe4e6', 'recipient_mode' => 'none', 'recipient_label' => 'To', 'recipient_office_id' => null, 'document_level' => 'Inter', 'number_prefix' => null, 'show_thru' => 1, 'show_carbon_copy' => 1, 'allow_attachments' => 1, 'requires_signatories' => 1, 'is_publicly_creatable' => 1, 'content_template' => null, 'print_layout' => 'indorsement', 'sender_signature_policy' => 'never', 'approver_display_mode' => 'action_box', 'allow_oic_signature' => 1],
                ['id' => 6, 'name' => 'Special Order', 'abbreviation' => 'SO', 'chip_color' => '#cffafe', 'recipient_mode' => 'none', 'recipient_label' => 'To', 'recipient_office_id' => null, 'document_level' => 'Inter', 'number_prefix' => null, 'show_thru' => 1, 'show_carbon_copy' => 1, 'allow_attachments' => 1, 'requires_signatories' => 1, 'is_publicly_creatable' => 0, 'content_template' => null, 'print_layout' => 'special_order', 'sender_signature_policy' => 'never', 'approver_display_mode' => 'signature_only', 'allow_oic_signature' => 1],
            ];
            $this->upsert('document_types', array_map(function ($type) {
                $type['recipient_office_id'] = null;

                return $type;
            }, $documentTypes));

            $offices = [
                ['id' => 1, 'name' => 'Office of the University President', 'abbreviation' => 'OP', 'office_type' => 'ADMIN', 'head_id' => 2],
                ['id' => 2, 'name' => 'Office of the Vice President for Adminstration and Finance', 'abbreviation' => 'OVPAF', 'office_type' => 'ADMIN', 'head_id' => 3],
                ['id' => 3, 'name' => 'Office of the Vice President for Academic Affairs', 'abbreviation' => 'OVPAA', 'office_type' => 'ADMIN', 'head_id' => 4],
                ['id' => 4, 'name' => 'Office of the Supervising Administrative Officer for Administration', 'abbreviation' => 'SAO', 'office_type' => 'ADMIN', 'head_id' => 5, 'acting_head_id' => 6],
                ['id' => 5, 'name' => 'Office of the Supervising Administrative Officer for Finance', 'abbreviation' => 'SAO-F', 'office_type' => 'ADMIN', 'head_id' => 7],
                ['id' => 6, 'name' => 'Office of the Chief Administrative Officer', 'abbreviation' => 'CAO', 'office_type' => 'ADMIN', 'head_id' => 8],
                ['id' => 7, 'name' => 'College of Information and Computing Sciences', 'abbreviation' => 'CICS', 'office_type' => 'ACAD', 'head_id' => 9],
                ['id' => 8, 'name' => 'Motorpool Office', 'abbreviation' => 'Motorpool', 'office_type' => 'ADMIN', 'head_id' => 10],
                ['id' => 9, 'name' => 'Information and Communications Technology Unit', 'abbreviation' => 'ICTU', 'office_type' => 'ADMIN', 'head_id' => 11],
                ['id' => 10, 'name' => 'Budget Office', 'abbreviation' => 'BO', 'office_type' => 'ADMIN', 'head_id' => 12],
                ['id' => 11, 'name' => 'Records Office', 'abbreviation' => 'RO', 'office_type' => 'ADMIN', 'head_id' => 13],
            ];
            $this->upsert('offices', array_map(function ($office) {
                $office['head_id'] = null;
                $office['acting_head_id'] = null;

                return $office + ['workflow_key' => null, 'office_logo' => null, 'deleted_at' => null];
            }, $offices));

            $this->seedUsers([
                ['id' => 1, 'name' => 'System Administrator', 'email' => 'admin@example.com', 'position' => 'Administrator', 'role_id' => 1, 'office_id' => null, 'signature' => null],
                ['id' => 2, 'name' => 'Nelson Cabral', 'email' => 'president@example.com', 'position' => 'president', 'role_id' => 3, 'office_id' => 1, 'signature' => 'assets/img/signatures/president.png'],
                ['id' => 3, 'name' => 'Josephine Sulasula', 'email' => 'vpaf@example.com', 'position' => 'vice-president', 'role_id' => 4, 'office_id' => 2, 'signature' => 'assets/img/signatures/vpaf.png'],
                ['id' => 4, 'name' => 'Maria Christina Wee', 'email' => 'vpaa@example.com', 'position' => 'vice-president', 'role_id' => 4, 'office_id' => 3, 'signature' => 'assets/img/signatures/vpaa.png'],
                ['id' => 5, 'name' => 'Arnel Lee', 'email' => 'sao-a@example.com', 'position' => 'sao', 'role_id' => 6, 'office_id' => 4, 'signature' => null],
                ['id' => 6, 'name' => 'Kristell Villanueva', 'email' => 'sao-a-oic@example.com', 'position' => 'sao', 'role_id' => 6, 'office_id' => 4, 'signature' => 'assets/img/signatures/sao-a.png'],
                ['id' => 7, 'name' => 'Maria Christina Vergara', 'email' => 'sao-f@example.com', 'position' => 'sao', 'role_id' => 6, 'office_id' => 5, 'signature' => 'assets/img/signatures/sao-f.png'],
                ['id' => 8, 'name' => 'Carina Abidin', 'email' => 'cao@example.com', 'position' => 'cao', 'role_id' => 5, 'office_id' => 6, 'signature' => 'assets/img/signatures/cao.png'],
                ['id' => 9, 'name' => 'Ferdinand Andrade', 'email' => 'cics@example.com', 'position' => 'head', 'role_id' => 2, 'office_id' => 7, 'signature' => 'assets/img/signatures/cics.png'],
                ['id' => 10, 'name' => 'Head Motorpool', 'email' => 'motorpool@example.com', 'position' => 'head', 'role_id' => 2, 'office_id' => 8, 'signature' => null],
                ['id' => 11, 'name' => 'Keith Carlou Jaramilla', 'email' => 'ictu@example.com', 'position' => 'head', 'role_id' => 2, 'office_id' => 9, 'signature' => 'assets/img/signatures/ictu.png'],
                ['id' => 12, 'name' => 'Zandra Gale Tablante', 'email' => 'budget@example.com', 'position' => 'head', 'role_id' => 2, 'office_id' => 10, 'signature' => null],
                ['id' => 13, 'name' => 'Bernadette Pagayonan', 'email' => 'records@example.com', 'position' => 'records', 'role_id' => 7, 'office_id' => 11, 'signature' => null],
            ]);

            $this->upsert('offices', array_map(fn ($office) => $office + ['acting_head_id' => null, 'workflow_key' => null, 'office_logo' => null, 'deleted_at' => null], $offices));
            $this->upsert('document_types', $documentTypes);

            $profiles = [
                [1, 2, 'Nelson', 'Cabral', 'male'], [2, 3, 'Josephine', 'Sulasula', 'female'],
                [3, 4, 'Maria Christina', 'Wee', 'female'], [4, 5, 'Arnel', 'Lee', 'male'],
                [5, 6, 'Kristell', 'Villanueva', 'female'], [6, 7, 'Maria Christina', 'Vergara', 'female'],
                [7, 8, 'Carina', 'Abidin', 'female'], [8, 9, 'Ferdinand', 'Andrade', 'male'],
                [9, 10, 'Head', 'Motorpool', 'male'], [10, 11, 'Keith Carlou', 'Jaramilla', 'male'],
                [11, 12, 'Zandra Gale', 'Tablante', 'female'], [12, 13, 'Bernadette', 'Pagayonan', 'female'],
            ];
            $this->upsert('user_profiles', array_map(fn ($profile) => [
                'id' => $profile[0], 'user_id' => $profile[1], 'honorifics' => '', 'given_name' => $profile[2],
                'middle_name' => '', 'middle_initial' => '', 'family_name' => $profile[3], 'suffix' => '',
                'titles' => '', 'gender' => $profile[4],
            ], $profiles));

            $permissionRoles = [[1,1],[2,1],[3,1],[4,1],[5,1],[6,2],[6,3],[6,4],[6,5],[6,6],[7,2],[7,3],[7,4],[7,5],[7,6],[7,7],[8,7],[9,2],[9,3],[9,4],[9,5],[9,6],[9,7],[10,2],[10,3],[10,4],[10,5],[10,6],[10,7]];
            $this->upsert('permission_role', array_map(fn ($row) => ['permission_id' => $row[0], 'role_id' => $row[1]], $permissionRoles), ['permission_id', 'role_id']);

            $roleTypes = [[1,1,2,0],[2,1,1,0],[3,7,2,0],[4,7,3,0],[5,7,4,0],[6,7,5,0],[7,7,6,0],[8,7,1,0],[9,2,2,0],[10,2,3,1],[11,2,4,1],[12,2,5,1],[13,2,6,0],[14,2,1,1],[15,6,2,1],[16,6,3,0],[17,6,4,0],[18,6,5,0],[19,6,6,1],[20,6,1,0],[21,3,2,1],[22,3,3,0],[23,3,4,0],[24,3,5,0],[25,3,6,1],[26,3,1,0]];
            $this->upsert('role_document_types', array_map(fn ($row) => ['id' => $row[0], 'role_id' => $row[1], 'document_type_id' => $row[2], 'is_allowed' => $row[3]], $roleTypes));

            $this->upsert('workflow_conditions', [
                ['id' => 1, 'key' => 'has_ict_implications', 'label' => 'Has ICT implications?', 'input_type' => 'boolean', 'options' => null, 'is_active' => 1],
                ['id' => 2, 'key' => 'has_budget_implications', 'label' => 'Has budget implications?', 'input_type' => 'boolean', 'options' => null, 'is_active' => 1],
            ]);

            $stages = [
                [1,1,8,'routing','Motorpool Review',1000,0,1,null,null],[2,1,9,'routing','ICTU Review',1010,1,1,1,'1'],
                [3,1,2,'signatory','Recommending Approval',2000,1,1,2,'1'],[4,1,3,'signatory','Recommending Approval',2010,0,1,null,null],
                [5,1,4,'action','Generation of IOM',3000,1,0,2,'0'],[6,1,5,'action','Generation of IOM',3010,1,0,2,'1'],
                [7,1,1,'signatory','Approved by',2020,1,1,null,null],[8,2,1,'signatory','Approved by',2000,1,1,null,null],
                [9,3,1,'signatory','Approved by',2000,1,1,null,null],[10,5,1,'signatory','Approved by',2000,1,1,null,null],
                [11,6,6,'routing','CAO Review',1000,1,0,null,null],[12,6,2,'routing','VPAF Review',1010,1,0,null,null],
                [13,6,1,'signatory','Approved by',2000,1,1,null,null],[15,1,10,'routing','Budget Review',1020,1,1,2,'1'],
            ];
            $this->upsert('document_flow_stages', array_map(fn ($row) => [
                'id' => $row[0], 'document_type_id' => $row[1], 'office_id' => $row[2], 'stage_type' => $row[3],
                'label' => $row[4], 'description' => null, 'sequence' => $row[5], 'is_required' => $row[6],
                'is_selectable' => $row[7], 'workflow_condition_id' => $row[8], 'condition_operator' => 'equals',
                'condition_value' => $row[9],
            ], $stages));

            $rules = [
                [1,'internal',1,2,'Generate IOM','Approved',0,1], [2,'external',null,1,'Generate RLM',null,0,1],
                [4,'internal',5,6,'Generate SO','Approved',0,1], [5,'internal',5,2,'Generate IOM','Approved',0,1],
                [6,'external',null,3,'Generate ECLR','Approved',0,1],
            ];
            $this->upsert('document_generation_rules', array_map(fn ($row) => [
                'id' => $row[0], 'source_context' => $row[1], 'source_document_type_id' => $row[2],
                'target_document_type_id' => $row[3], 'button_label' => $row[4], 'required_status' => $row[5],
                'requires_assigned_office' => $row[6], 'is_active' => $row[7],
            ], $rules));

            $ruleRoles = [[1,6],[2,2],[2,3],[2,4],[2,5],[2,6],[2,7],[4,6],[5,6],[6,1],[6,2],[6,3],[6,4],[6,5],[6,6],[6,7]];
            $this->upsert('document_generation_rule_role', array_map(fn ($row) => ['document_generation_rule_id' => $row[0], 'role_id' => $row[1]], $ruleRoles), ['document_generation_rule_id', 'role_id']);
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function seedUsers(array $users): void
    {
        foreach ($users as $user) {
            $attributes = $user + ['email_verified_at' => null, 'avatar' => null, 'deleted_at' => null];

            if (DB::table('users')->where('id', $user['id'])->exists()) {
                DB::table('users')->where('id', $user['id'])->update($attributes);
            } else {
                DB::table('users')->insert($attributes + ['password' => Hash::make('password')]);
            }
        }
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
