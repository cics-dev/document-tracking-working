<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\Office;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DocumentType::create([
            'name' => 'Request Letter Memorandum',
            'abbreviation' => 'RLM'
        ]);
        DocumentType::create([
            'name' => 'Inter-Office Memorandum',
            'abbreviation' => 'IOM'
        ]);
        DocumentType::create([
            'name' => 'Indorsement Letter',
            'abbreviation' => ''
        ]);
        DocumentType::create([
            'name' => 'Special Order',
            'abbreviation' => 'SO'
        ]);



        Office::create([
            'name' => 'Office of the University President',
            'abbreviation' => 'SAO',
            'office_type' => 'ADMIN'
        ]);
        Office::create([
            'name' => 'Office of the Vice President for Student Affairs and Services',
            'abbreviation' => 'VPSAS',
            'office_type' => 'ADMIN'
        ]);
        Office::create([
            'name' => 'Office of the Vice President for Academic Affairs',
            'abbreviation' => 'VPAA',
            'office_type' => 'ADMIN'
        ]);
        Office::create([
            'name' => 'Office of the Vice President for Administration and Finance',
            'abbreviation' => 'VPAF',
            'office_type' => 'ADMIN'
        ]);
        Office::create([
            'name' => 'Office of the Vice President for Research Development and Extension',
            'abbreviation' => 'VPRDE',
            'office_type' => 'ADMIN'
        ]);
        Office::create([
            'name' => 'Disaster Risk Reduction and Management Office',
            'abbreviation' => 'DRRMO',
            'office_type' => ''
        ]);


        Office::create([
            'name' => 'College of Arts, Humanities and Social Sciences',
            'abbreviation' => 'CAHSS',
            'office_type' => 'ACAD'
        ]);
        Office::create([
            'name' => 'College of Engineering and Technology',
            'abbreviation' => 'CET',
            'office_type' => 'ACAD'
        ]);
        Office::create([
            'name' => 'College of Information and Computing Sciences',
            'abbreviation' => 'CICS',
            'office_type' => 'ACAD',
            'office_logo' => 'office_images/cics-logo.jpg'
        ]);
        Office::create([
            'name' => 'College of Maritime Education',
            'abbreviation' => 'CME',
            'office_type' => 'ACAD'
        ]);
        Office::create([
            'name' => 'College of Physical Education and Sports',
            'abbreviation' => 'CPES',
            'office_type' => 'ACAD'
        ]);
        Office::create([
            'name' => 'College of Teacher Education',
            'abbreviation' => 'CTE',
            'office_type' => 'ACAD'
        ]);
        Office::create([
            'name' => 'External Program Delivering Unit',
            'abbreviation' => 'EPDU',
            'office_type' => 'ACAD'
        ]);
        Office::create([
            'name' => 'Institute of Technical Education',
            'abbreviation' => 'ITE',
            'office_type' => 'ACAD'
        ]);
        Office::create([
            'name' => 'School of Business Administration',
            'abbreviation' => 'SBA',
            'office_type' => 'ACAD'
        ]);
        Office::create([
            'name' => 'Senior High School',
            'abbreviation' => 'SHS',
            'office_type' => 'ACAD'
        ]);

        Office::create([
            'name' => 'Records Section',
            'abbreviation' => 'Records Section',
            'office_type' => ''
        ]);

        Office::create([
            'name' => 'Administration',
            'abbreviation' => 'SAO',
            'office_type' => 'ADMIN'
        ]);
        
        Office::create([
            'name' => 'Budget Office',
            'abbreviation' => 'Budget Office',
            'office_type' => ''
        ]);
        
        Office::create([
            'name' => 'Motorpool Office',
            'abbreviation' => 'Motorpool Office',
            'office_type' => ''
        ]);
        
        Office::create([
            'name' => 'Legal Office',
            'abbreviation' => 'Legal Office',
            'office_type' => ''
        ]);
        
        Office::create([
            'name' => 'Income Generating Program Office',
            'abbreviation' => 'IGP Office',
            'office_type' => ''
        ]);
    }
}
