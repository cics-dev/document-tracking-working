<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            ['Office of the University President', 'OP', 'university_president', 'ADMIN', null],
            ['Office of the Vice President for Student Affairs and Services', 'VPSAS', null, 'ADMIN', null],
            ['Office of the Vice President for Academic Affairs', 'VPAA', null, 'ADMIN', null],
            ['Office of the Vice President for Administration and Finance', 'VPAF', 'vpaf', 'ADMIN', null],
            ['Office of the Vice President for Research Development and Extension', 'VPRDE', null, 'ADMIN', null],
            ['Disaster Risk Reduction and Management Office', 'DRRMO', null, '', null],
            ['College of Arts, Humanities and Social Sciences', 'CAHSS', null, 'ACAD', null],
            ['College of Engineering and Technology', 'CET', null, 'ACAD', null],
            ['College of Information and Computing Sciences', 'CICS', null, 'ACAD', 'office_images/cics-logo.jpg'],
            ['College of Maritime Education', 'CME', null, 'ACAD', null],
            ['College of Physical Education and Sports', 'CPES', null, 'ACAD', null],
            ['College of Teacher Education', 'CTE', null, 'ACAD', null],
            ['External Program Delivering Unit', 'EPDU', null, 'ACAD', null],
            ['Institute of Technical Education', 'ITE', null, 'ACAD', null],
            ['School of Business Administration', 'SBA', null, 'ACAD', null],
            ['Senior High School', 'SHS', null, 'ACAD', null],
            ['Records Section', 'Records Section', 'records', '', null],
            ['Office of the Supervising Administrative Officer for Admin', 'SAO-A', 'sao_admin', 'ADMIN', null],
            ['Budget Office', 'Budget Office', 'budget', '', null],
            ['Motorpool Office', 'Motorpool Office', 'motor_pool', '', null],
            ['Legal Office', 'Legal Office', 'legal', '', null],
            ['Income Generating Program Office', 'IGP Office', 'igp', '', null],
            ['Office of the Chief Administrative Officer', 'CAO', 'cao', '', null],
            ['Information and Communications Technology Unit', 'ICTU', null, '', null],
            ['University Registrar', 'Registrar', null, '', null],
            ['Public Information Office', 'PIO', null, '', null],
            ['Office of the Supervising Administrative Officer for Finance', 'SAO-F', 'sao_finance', 'ADMIN', null],
        ];

        foreach ($offices as [$name, $abbreviation, $workflowKey, $officeType, $officeLogo]) {
            $office = Office::withTrashed()->updateOrCreate(
                ['abbreviation' => $abbreviation],
                [
                    'name' => $name,
                    'workflow_key' => $workflowKey,
                    'office_type' => $officeType,
                    'office_logo' => $officeLogo,
                ],
            );
            $office->restore();
        }
    }
}
