<?php

namespace Database\Seeders;

use App\Models\DocumentFlowStage;
use App\Models\DocumentType;
use App\Models\Office;
use Illuminate\Database\Seeder;

class DocumentFlowSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFlow('RLM', [
            ['budget', 'routing', 'Budget Office Review', 10, false, true, 'always'],
            ['motor_pool', 'routing', 'Motor Pool Review', 20, false, true, 'always'],
            ['legal', 'routing', 'Legal/Compliance Review', 30, false, true, 'always'],
            ['igp', 'routing', 'IGP Review', 40, false, true, 'always', 'For gymnasium or any school facility usage.'],
            ['university_president', 'signatory', 'Approved by', 80, true, false, 'always'],
            ['sao_finance', 'action', 'Generation of IOM', 90, true, false, 'with_budget'],
            ['sao_admin', 'action', 'Generation of IOM', 90, true, false, 'without_budget'],
        ]);

        $this->seedFlow('SO', [
            ['cao', 'routing', 'Chief Administrative Officer Review', 10, true, false, 'always'],
            ['vpaf', 'routing', 'VPAF Review', 20, true, false, 'always'],
            ['university_president', 'signatory', 'Approved by', 30, true, false, 'always'],
        ]);
    }

    private function seedFlow(string $abbreviation, array $stages): void
    {
        $type = DocumentType::where('abbreviation', $abbreviation)->first();
        if (! $type) return;

        foreach ($stages as $stage) {
            [$officeKey, $stageType, $label, $sequence, $required, $selectable, $condition] = $stage;
            $description = $stage[7] ?? null;
            $office = Office::where('workflow_key', $officeKey)->first();
            if (! $office) continue;
            DocumentFlowStage::updateOrCreate(
                ['document_type_id' => $type->id, 'office_id' => $office->id, 'stage_type' => $stageType, 'condition' => $condition],
                compact('label', 'description', 'sequence') + ['is_required' => $required, 'is_selectable' => $selectable]
            );
        }
    }
}
