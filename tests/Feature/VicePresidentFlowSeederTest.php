<?php

use App\Models\DocumentFlowStage;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\IndorsementFlowSeeder;
use Database\Seeders\RlmVicePresidentFlowSeeder;
use Database\Seeders\SetupDataSeeder;

it('adds every VP to RLM and IL while preserving existing RLM conditions', function () {
    $this->seed(SetupDataSeeder::class);
    $vpRole = Role::where('role', 'vice-president')->sole();
    foreach (['Future VP'] as $name) {
        $office = Office::create(['name' => $name, 'abbreviation' => $name, 'office_type' => 'ADMIN']);
        $head = User::factory()->create(['office_id' => $office->id, 'role_id' => $vpRole->id]);
        $office->update(['head_id' => $head->id]);
    }
    $rlm = DocumentType::where('abbreviation', 'RLM')->sole();
    $original = $rlm->flowStages()->get()->keyBy('id')->toArray();
    $this->seed([IndorsementFlowSeeder::class, RlmVicePresidentFlowSeeder::class]);
    $this->seed([IndorsementFlowSeeder::class, RlmVicePresidentFlowSeeder::class]);

    $offices = Office::whereHas('head', fn ($q) => $q->where('role_id', $vpRole->id))->get();
    expect($offices)->toHaveCount(6);
    foreach ($offices as $office) {
        foreach (['RLM', 'IL'] as $abbreviation) {
            $stages = DocumentFlowStage::whereHas('documentType', fn ($q) => $q->where('abbreviation', $abbreviation))
                ->where('office_id', $office->id)->where('stage_type', 'signatory')->get();
            expect($stages)->toHaveCount(1);
            expect($stages->first()->label)->toBe('Recommending Approval');
            if ($abbreviation === 'IL') {
                expect($stages->first()->selection_group)->toBe('VP approval');
            }
        }
    }
    foreach ($original as $id => $attributes) {
        expect(DocumentFlowStage::findOrFail($id)->toArray())->toBe($attributes);
    }
});
