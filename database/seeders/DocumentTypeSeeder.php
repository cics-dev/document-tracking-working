<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Request Letter Memorandum', 'RLM'],
            ['Inter-Office Memorandum', 'IOM'],
            ['Indorsement Letter', 'IL'],
            ['Special Order', 'SO'],
            ['External Communication Response Letter', 'ECLR'],
            ['Intra-Office Memorandum', 'Intra'],
        ] as [$name, $abbreviation]) {
            DocumentType::updateOrCreate(['abbreviation' => $abbreviation], ['name' => $name]);
        }
    }
}
