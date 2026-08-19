<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Request Letter Memorandum', 'RLM', 'memorandum', 'always', 'action_box'],
            ['Inter-Office Memorandum', 'IOM', 'memorandum', 'approved', 'labeled'],
            ['Indorsement Letter', 'IL', 'indorsement', 'approved', 'action_box'],
            ['Special Order', 'SO', 'special_order', 'approved', 'labeled'],
            ['External Communication Response Letter', 'ECLR', 'letter', 'approved', 'labeled'],
            ['Intra-Office Memorandum', 'Intra', 'memorandum', 'approved', 'labeled'],
        ] as [$name, $abbreviation, $printLayout, $senderSignaturePolicy, $approverDisplayMode]) {
            DocumentType::updateOrCreate(['abbreviation' => $abbreviation], [
                'name' => $name,
                'print_layout' => $printLayout,
                'sender_signature_policy' => $senderSignaturePolicy,
                'approver_display_mode' => $approverDisplayMode,
                'allow_oic_signature' => true,
            ]);
        }
    }
}
