<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conditionId = DB::table('workflow_conditions')->where('key', 'has_budget_implications')->value('id');
        $budgetOfficeId = DB::table('offices')->where('workflow_key', 'budget')->value('id');
        if ($conditionId && $budgetOfficeId) {
            DB::table('document_flow_stages')->where('office_id', $budgetOfficeId)->where('stage_type', 'routing')->update([
                'workflow_condition_id' => $conditionId, 'condition_operator' => 'equals', 'condition_value' => '1',
                'is_required' => true, 'is_selectable' => true, 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $budgetOfficeId = DB::table('offices')->where('workflow_key', 'budget')->value('id');
        if ($budgetOfficeId) DB::table('document_flow_stages')->where('office_id', $budgetOfficeId)->where('stage_type', 'routing')->update(['is_selectable' => false, 'updated_at' => now()]);
    }
};
