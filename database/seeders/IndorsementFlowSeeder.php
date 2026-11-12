<?php

namespace Database\Seeders;

use App\Models\DocumentFlowStage;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IndorsementFlowSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $type = DocumentType::where('abbreviation', 'IL')->sole();
            $vpRole = Role::whereIn('role', ['vice-president', 'vp'])->sole();
            $presidentRole = Role::whereIn('role', ['president', 'pres'])->sole();
            $president = Office::whereHas('head', fn ($query) => $query->where('role_id', $presidentRole->id))->sole();
            $vicePresidents = Office::whereHas('head', fn ($query) => $query->where('role_id', $vpRole->id))->get();

            if ($vicePresidents->isEmpty()) {
                throw new RuntimeException('Configure at least one VP office head before seeding IL flows.');
            }

            $defaults = [
                'is_required' => true,
                'is_selectable' => false,
                'workflow_condition_id' => null,
                'condition_operator' => 'equals',
                'condition_value' => null,
                'sender_role_id' => $vpRole->id,
            ];

            DocumentFlowStage::updateOrCreate([
                'document_type_id' => $type->id,
                'office_id' => $president->id,
                'stage_type' => 'signatory',
            ], array_replace($defaults, [
                'label' => 'Approved by',
                'sequence' => 2020,
                'sender_role_id' => null,
                'sender_role_operator' => 'equals',
                'selection_group' => null,
            ]));

            foreach ($vicePresidents as $office) {
                DocumentFlowStage::updateOrCreate([
                    'document_type_id' => $type->id,
                    'office_id' => $office->id,
                    'stage_type' => 'signatory',
                ], array_replace($defaults, [
                    'label' => 'Recommending Approval',
                    'sequence' => 2000,
                    'is_selectable' => true,
                    'sender_role_operator' => 'not_equals',
                    'selection_group' => 'VP approval',
                ]));
            }
        });
    }
}
