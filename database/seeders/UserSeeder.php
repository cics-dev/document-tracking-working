<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'position' => 'Administrator',
            'role_id' => 1,
            'office_id' => null, // set if needed
        ]);

        $president = User::create([
            'name' => 'Nelson P. Cabral',
            'email' => 'president@example.com',
            'password' => Hash::make('password'),
            'position' => 'University President',
            'role_id' => 2,
            'signature' => 'assets/img/fakesig1.png',
            'office_id' => 1, // set if needed
        ]);
        $president->profile()->create([
            'honorifics' => 'Mr',
            'given_name' => 'Nelson',
            'middle_name' => 'P',
            'middle_initial' => 'P',
            'family_name' => 'Cabral',
            'suffix' => '',
            'titles' => 'Ed.D.',
            'gender' => 'Male',
        ]);
        $president->office()->update([
            'head_id' => $president->id
        ]);
        
        $vpsas = User::create([
            'name' => 'Cyrus Pil P. Cadavedo',
            'email' => 'vpsas@example.com',
            'password' => Hash::make('password'),
            'position' => 'Vice President for Student Affairs and Services',
            'role_id' => 3,
            'signature' => 'assets/img/fakesig2.png',
            'office_id' => 2, // set if needed
        ]);
        $vpsas->profile()->create([
            'honorifics' => 'Dr',
            'given_name' => 'Cyrus Pil',
            'middle_name' => 'P',
            'middle_initial' => 'P',
            'family_name' => 'Cadavedo',
            'suffix' => '',
            'titles' => 'Ph.D.',
            'gender' => 'Male',
        ]);
        $vpsas->office()->update([
            'head_id' => $vpsas->id
        ]);
        
        $vpaa = User::create([
            'name' => 'Maria Christina G. Wee',
            'email' => 'vpaa@example.com',
            'password' => Hash::make('password'),
            'position' => 'Vice President for Academic Affairs',
            'role_id' => 3,
            'signature' => 'assets/img/fakesig3.png',
            'office_id' => 3, // set if needed
        ]);
        $vpaa->profile()->create([
            'honorifics' => 'Dr',
            'given_name' => 'Maria Christina',
            'middle_name' => 'G',
            'middle_initial' => 'G',
            'family_name' => 'Wee',
            'suffix' => '',
            'titles' => 'Ph.D.',
            'gender' => 'Female',
        ]);
        $vpaa->office()->update([
            'head_id' => $vpaa->id
        ]);
        
        $vpaf = User::create([
            'name' => 'Josephine L. Sulasula',
            'email' => 'vpaf@example.com',
            'password' => Hash::make('password'),
            'position' => 'Vice President for Administration and Finance',
            'role_id' => 3,
            'signature' => 'assets/img/fakesig4.png',
            'office_id' => 4, // set if needed
        ]);
        $vpaf->profile()->create([
            'honorifics' => 'Dr',
            'given_name' => 'Josephine',
            'middle_name' => 'L',
            'middle_initial' => 'L',
            'family_name' => 'Sulasula',
            'suffix' => '',
            'titles' => 'Ph.D.',
            'gender' => 'Female',
        ]);
        $vpaf->office()->update([
            'head_id' => $vpaf->id
        ]);
        
        $vprde = User::create([
            'name' => 'Rolando P. Malalay',
            'email' => 'vprde@example.com',
            'password' => Hash::make('password'),
            'position' => 'Vice President for Research Development and Extension',
            'role_id' => 3, 
            'signature' => 'assets/img/fakesig5.png',
            'office_id' => 5, // set if needed
        ]);
        $vprde->profile()->create([
            'honorifics' => 'Dr',
            'given_name' => 'Rolando',
            'middle_name' => 'P',
            'middle_initial' => 'P',
            'family_name' => 'Malalay',
            'suffix' => '',
            'titles' => 'Ph.D.',
            'gender' => 'Male',
        ]);
        $vprde->office()->update([
            'head_id' => $vprde->id
        ]);
        
        $cics_dean = User::create([
            'name' => 'Ferdinand V. Andrade',
            'email' => 'cics_dean@example.com',
            'password' => Hash::make('password'),
            'position' => 'Dean',
            'role_id' => 8,
            'office_id' => 9, // set if needed
        ]);
        $cics_dean->profile()->create([
            'honorifics' => 'Mr',
            'given_name' => 'Ferdinand',
            'middle_name' => 'V',
            'middle_initial' => 'V',
            'family_name' => 'Andrade',
            'suffix' => '',
            'titles' => 'MIT',
            'gender' => 'Male',
        ]);
        $cics_dean->office()->update([
            'head_id' => $cics_dean->id
        ]);
        
        $drrmo = User::create([
            'name' => 'Michael M. Cabiles',
            'email' => 'drrmo@example.com',
            'password' => Hash::make('password'),
            'position' => 'Director',
            'role_id' => 9,
            'office_id' => 6, // set if needed
        ]);
        $drrmo->profile()->create([
            'honorifics' => 'Mr',
            'given_name' => 'Michael',
            'middle_name' => 'M',
            'middle_initial' => 'M',
            'family_name' => 'Cabiles',
            'suffix' => '',
            'titles' => 'Ed.D.',
            'gender' => 'Male',
        ]);
        $drrmo->office()->update([
            'head_id' => $drrmo->id
        ]);

        $records = User::create([
            'name' => 'Unknown',
            'email' => 'records@example.com',
            'password' => Hash::make('password'),
            'position' => 'Records Officer',
            'role_id' => 10,
            'office_id' => 17, // set if needed
        ]);
        $records->profile()->create([
            'honorifics' => 'Unknown',
            'given_name' => 'Unknown',
            'middle_name' => 'Unknown',
            'middle_initial' => 'Unknown',
            'family_name' => 'Unknown',
            'suffix' => '',
            'titles' => '',
            'gender' => 'Unknown',
        ]);
        $records->office()->update([
            'head_id' => $records->id
        ]);

        $sao = User::create([
            'name' => 'Arnel H. Lee',
            'email' => 'sao-a@example.com',
            'password' => Hash::make('password'),
            'position' => 'Supervising Administrative Officer for Administration',
            'role_id' => 4,
            'office_id' => 18, // set if needed
        ]);
        $sao->profile()->create([
            'honorifics' => 'Mr',
            'given_name' => 'Arnel',
            'middle_name' => 'H',
            'middle_initial' => 'H',
            'family_name' => 'Lee',
            'suffix' => '',
            'titles' => '',
            'gender' => 'Male',
        ]);
        $sao->office()->update([
            'head_id' => $sao->id
        ]);

        $budget = User::create([
            'name' => 'Unknown',
            'email' => 'budget@example.com',
            'password' => Hash::make('password'),
            'position' => 'Budget Office Head',
            'role_id' => 10,
            'office_id' => 19, // set if needed
        ]);
        $budget->profile()->create([
            'honorifics' => 'Unknown',
            'given_name' => 'Unknown',
            'middle_name' => 'Unknown',
            'middle_initial' => 'Unknown',
            'family_name' => 'Unknown',
            'suffix' => '',
            'titles' => '',
            'gender' => 'Unknown',
        ]);
        $budget->office()->update([
            'head_id' => $budget->id
        ]);

        $motorpool = User::create([
            'name' => 'Unknown',
            'email' => 'motorpool@example.com',
            'password' => Hash::make('password'),
            'position' => 'Motorpool Office Head',
            'role_id' => 10,
            'office_id' => 20, // set if needed
        ]);
        $motorpool->profile()->create([
            'honorifics' => 'Unknown',
            'given_name' => 'Unknown',
            'middle_name' => 'Unknown',
            'middle_initial' => 'Unknown',
            'family_name' => 'Unknown',
            'suffix' => '',
            'titles' => '',
            'gender' => 'Unknown',
        ]);
        $motorpool->office()->update([
            'head_id' => $motorpool->id
        ]);

        $legal = User::create([
            'name' => 'Unknown',
            'email' => 'legal@example.com',
            'password' => Hash::make('password'),
            'position' => 'University Attorney',
            'role_id' => 10,
            'office_id' => 21, // set if needed
        ]);
        $legal->profile()->create([
            'honorifics' => 'Unknown',
            'given_name' => 'Unknown',
            'middle_name' => 'Unknown',
            'middle_initial' => 'Unknown',
            'family_name' => 'Unknown',
            'suffix' => '',
            'titles' => '',
            'gender' => 'Unknown',
        ]);
        $legal->office()->update([
            'head_id' => $legal->id
        ]);

        $igp = User::create([
            'name' => 'Unknown',
            'email' => 'igp@example.com',
            'password' => Hash::make('password'),
            'position' => 'IGP Office Head',
            'role_id' => 10,
            'office_id' => 22, // set if needed
        ]);
        $igp->profile()->create([
            'honorifics' => 'Unknown',
            'given_name' => 'Unknown',
            'middle_name' => 'Unknown',
            'middle_initial' => 'Unknown',
            'family_name' => 'Unknown',
            'suffix' => '',
            'titles' => '',
            'gender' => 'Unknown',
        ]);
        $igp->office()->update([
            'head_id' => $igp->id
        ]);
    }
}
