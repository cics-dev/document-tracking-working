<?php

namespace Tests\Feature;

use App\Livewire\Documents\CreateDocument;
use App\Models\Document;
use App\Models\DocumentFlowStage;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class ConfiguredDocumentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_configured_flow_includes_required_and_selected_stages_and_uses_budget_branch(): void
    {
        $type = DocumentType::create(['name' => 'Request Letter Memorandum', 'abbreviation' => 'RLM']);
        $optional = $this->office('Optional Review', 'OR');
        $optional->update(['workflow_key' => 'budget']);
        $president = $this->office('President', 'P');
        $recommender = $this->office('Recommending Office', 'RO');
        $noted = $this->office('Any Noting Office', 'NO');
        $finance = $this->office('Finance', 'F');
        $admin = $this->office('Administration', 'A');

        $optionalStage = $this->stage($type, $optional, 'routing', 'Optional Review', 10, false, true);
        $this->stage($type, $president, 'signatory', 'Approved by', 20, true, false);
        $this->stage($type, $recommender, 'signatory', 'Recommending Approval', 15, false, true);
        $this->stage($type, $finance, 'action', 'Generate IOM', 30, true, false, 'with_budget');
        $this->stage($type, $admin, 'action', 'Generate IOM', 30, true, false, 'without_budget');

        $document = Document::create([
            'document_number' => 'RLM-1-2026', 'from_id' => $optional->id, 'to_id' => $president->id,
            'document_type_id' => $type->id, 'subject' => 'Test', 'content' => 'Test', 'status' => 'Sent',
            'created_by' => $optional->head_id,
        ]);

        $component = new CreateDocument;
        $component->document_type = 'RLM';
        $component->document_type_id = (string) $type->id;
        $component->flowStages = DocumentFlowStage::where('document_type_id', $type->id)->get()->toArray();
        $component->selectedFlowStages = [(string) $optionalStage->id => true];
        $component->hasBudgetImplications = true;
        $component->signatories = [
            ['role' => 'Recommending Approval', 'office_id' => $recommender->id],
            ['role' => 'Noted by', 'office_id' => $noted->id],
        ];
        $component->cf_offices = [];

        $method = new ReflectionMethod(CreateDocument::class, 'processDocumentSteps');
        $method->setAccessible(true);
        $method->invoke($component, $document);

        $this->assertSame(
            [$optional->id, $recommender->id, $noted->id, $president->id, $finance->id],
            $document->steps()->orderBy('sequence')->pluck('office_id')->all()
        );
        $this->assertFalse($document->steps()->where('office_id', $admin->id)->exists());
    }

    public function test_il_without_a_configured_flow_does_not_get_an_implicit_president_step(): void
    {
        $type = DocumentType::create(['name' => 'Indorsement Letter', 'abbreviation' => 'IL']);
        $sender = $this->office('Sender', 'S');
        $document = Document::create([
            'document_number' => 'S-IL-1-2026', 'from_id' => $sender->id,
            'document_type_id' => $type->id, 'subject' => 'IL', 'content' => 'Test',
            'created_by' => $sender->head_id, 'status' => 'Sent',
        ]);
        $component = new CreateDocument;
        $component->document_type = 'IL';
        $component->document_type_id = (string) $type->id;
        $component->flowStages = [];
        $component->cf_offices = [];

        $method = new ReflectionMethod(CreateDocument::class, 'processDocumentSteps');
        $method->setAccessible(true);
        $method->invoke($component, $document);

        $this->assertCount(0, $document->steps);
    }

    private function office(string $name, string $abbreviation): Office
    {
        $office = Office::create(compact('name', 'abbreviation') + ['office_type' => 'ADMIN']);
        $head = User::factory()->create(['office_id' => $office->id]);
        $office->update(['head_id' => $head->id]);
        return $office->fresh();
    }

    private function stage(DocumentType $type, Office $office, string $stageType, string $label, int $sequence, bool $required, bool $selectable, string $condition = 'always'): DocumentFlowStage
    {
        return DocumentFlowStage::create([
            'document_type_id' => $type->id, 'office_id' => $office->id, 'stage_type' => $stageType,
            'label' => $label, 'sequence' => $sequence, 'is_required' => $required,
            'is_selectable' => $selectable, 'condition' => $condition,
        ]);
    }
}
