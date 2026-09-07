<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            OfficeSeeder::class,
<<<<<<< HEAD
            RoleSeeder::class,
=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
            UserSeeder::class
        ]);
    }
}
