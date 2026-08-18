<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\Role;
use App\Models\RoleDocumentType;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'role' => 'admin',
                'description' => 'Administrator',
            ],
            [
                'role' => 'pres',
                'description' => 'President',
            ],
            [
                'role' => 'vp',
                'description' => 'Vice President',
            ],
            [
                'role' => 'sao-a',
                'description' => 'Supervising Administrative Officer - Admin',
            ],
            [
                'role' => 'sao-f',
                'description' => 'Supervising Administrative Officer - Finance',
            ],
            [
                'role' => 'cao',
                'description' => 'Chief Administrative Officer',
            ],
            [
                'role' => 'boardsec',
                'description' => 'Board Secretary',
            ],
            [
                'role' => 'dean',
                'description' => 'College Dean',
            ],
            [
                'role' => 'director',
                'description' => 'Office Director',
            ],
            [
                'role' => 'head',
                'description' => 'Unit Head',
            ],
            [
                'role' => 'principal',
                'description' => 'High School Principal',
            ],
            [
                'role' => 'chairperson',
                'description' => 'Committee Chairperson',
            ],
            [
                'role' => 'proponent',
                'description' => 'Event Proponent',
            ],
            [
                'role' => 'staff',
                'description' => 'Staff',
            ],
        ];

        $accessMap = [
            'admin' => 'all',
            'pres' => ['IOM', 'IL', 'SO', 'ECLR', 'Intra'],
            'vp' => ['RLM', 'IOM', 'IL', 'ECLR', 'Intra'],
            'sao-a' => 'all',
            'sao-f' => ['RLM', 'IOM', 'IL', 'ECLR', 'Intra'],
            'cao' => ['RLM', 'IL', 'ECLR', 'Intra'],
            'boardsec' => ['RLM', 'IOM', 'IL', 'ECLR', 'Intra'],
            'dean' => ['RLM', 'IL', 'ECLR', 'Intra'],
            'director' => ['RLM', 'IL', 'ECLR', 'Intra'],
            'head' => ['RLM', 'IL', 'ECLR', 'Intra'],
            'principal' => ['RLM', 'IL', 'ECLR', 'Intra'],
            'chairperson' => ['RLM', 'IL', 'ECLR', 'Intra'],
            'proponent' => ['RLM'],
            'staff' => [],
        ];

        $documentTypes = DocumentType::query()->get(['id', 'abbreviation']);

        foreach ($roles as $role) {
            $db_role = Role::updateOrCreate(
                ['role' => $role['role']],
                ['description' => $role['description']],
            );

            $allowed = $accessMap[$role['role']] ?? [];
            $entries = $documentTypes->map(fn (DocumentType $type) => [
                'role_id' => $db_role->id,
                'document_type_id' => $type->id,
                'is_allowed' => $allowed === 'all' || in_array($type->abbreviation, $allowed, true),
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

            RoleDocumentType::upsert(
                $entries,
                ['role_id', 'document_type_id'],
                ['is_allowed', 'updated_at'],
            );
        }
    }
}
