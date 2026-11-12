<?php

namespace Database\Seeders;

use App\Models\DocumentFlowStage;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RlmVicePresidentFlowSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $type = DocumentType::where('abbreviation', 'RLM')->sole();
            $vpRole = Role::whereIn('role', ['vice-president', 'vp'])->sole();
            $offices = Office::whereHas('head', fn ($query) => $query->where('role_id', $vpRole->id))->get();
            $approvalSequence = $type->flowStages()->where('stage_type', 'signatory')
                ->where('label', 'Approved by')->min('sequence') ?? 2020;

            foreach ($offices as $office) {
                DocumentFlowStage::firstOrCreate([
                    'document_type_id' => $type->id,
                    'office_id' => $office->id,
                    'stage_type' => 'signatory',
                ], [
                    'label' => 'Recommending Approval',
                    'sequence' => max(0, $approvalSequence - 1),
                    'is_required' => false,
                    'is_selectable' => true,
                ]);
            }
        });
    }
}
