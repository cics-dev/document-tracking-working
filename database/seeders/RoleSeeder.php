<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\DocumentType;

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
                'description' => 'Administrator'
            ],
            [
                'role' => 'pres',
                'description' => 'President'
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
            ]
        ];


        $accessMap = [
            1  => 'all',
            2  => [2, 3, 4, 5, 6],
            3  => [1, 2, 3, 5, 6],
            4  => 'all',
            5  => [1, 2, 3, 5, 6],
            6  => [1, 3, 5, 6],
            7  => [1, 2, 3, 5, 6],
            8  => [1, 3, 5, 6],
            9  => [1, 3, 5, 6],
            10 => [1, 3, 5, 6],
            11 => [1, 3, 5, 6],
            12 => [1, 3, 5, 6],
            13 => [1],
        ];


        $allDocumentTypes = DocumentType::all()->pluck('id')->toArray();

        foreach ($roles as $role) {
            $db_role = Role::create([
                'role' => $role['role'],
                'description' => $role['description']
            ]);

            $roleId = $db_role->id;

            // Determine allowed document types
            if (!isset($accessMap[$roleId])) {
                $allowed = [];
            } elseif ($accessMap[$roleId] === 'all') {
                $allowed = $allDocumentTypes;
            } else {
                $allowed = $accessMap[$roleId];
            }

            // Prepare all entries (true for allowed, false for not allowed)
            $entries = [];
            foreach ($allDocumentTypes as $docTypeId) {
                $entries[] = [
                    'document_type_id' => $docTypeId,
                    'is_allowed'       => in_array($docTypeId, $allowed),
                ];
            }

            // Bulk insert for efficiency
            $db_role->role_document_types()->createMany($entries);
        }
    }
}
