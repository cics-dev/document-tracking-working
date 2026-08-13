<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DocumentTypeSeeder::class,
            RoleSeeder::class,
            OfficeSeeder::class,
            UserSeeder::class,
            AccessRightSeeder::class,
            DocumentFlowSeeder::class,
        ]);
    }
}
