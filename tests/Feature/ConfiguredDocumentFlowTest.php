<?php

namespace Tests\Feature;

use App\Livewire\Documents\CreateDocument;
use App\Models\Document;
use App\Models\DocumentFlowStage;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\User;
use App\Models\WorkflowCondition;
use App\Services\DocumentWorkflowService;
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
        $component->conditionValues = [
            (string) WorkflowCondition::where('key', 'has_budget_implications')->value('id') => true,
        ];
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

    public function test_new_signatory_step_created_during_oic_period_names_the_oic_without_for(): void
    {
        $type = DocumentType::create(['name' => 'OIC Letter', 'abbreviation' => 'OIC']);
        $sender = $this->office('Sender', 'S');
        $recipient = $this->office('Vice President Office', 'VPO');
        $oic = User::factory()->create([
            'name' => 'Elena Garcia',
            'office_id' => $recipient->id,
            'signature' => 'signatures/elena.png',
        ]);
        $recipient->update(['acting_head_id' => $oic->id]);
        $stage = $this->stage($type, $recipient, 'signatory', 'Approved by', 1, true, false);
        $document = Document::create([
            'document_number' => 'S-OIC-1-2026', 'from_id' => $sender->id, 'to_id' => $recipient->id,
            'document_type_id' => $type->id, 'subject' => 'New during OIC period', 'content' => 'Test',
            'created_by' => $sender->head_id, 'status' => 'Sent',
        ]);
        $component = new CreateDocument;
        $component->document_type_id = (string) $type->id;
        $component->flowStages = [$stage->load('office', 'workflowCondition')->toArray()];
        $component->signatories = [];
        $component->cf_offices = [];

        $method = new ReflectionMethod(CreateDocument::class, 'processDocumentSteps');
        $method->setAccessible(true);
        $method->invoke($component, $document);

        $step = $document->steps()->firstOrFail();
        $this->assertSame($oic->id, $step->user_id);
        $this->assertSame('Elena Garcia', $step->signatory_name);
        $this->assertSame('Officer-in-Charge, Vice President Office', $step->signatory_position);

        app(DocumentWorkflowService::class)->approve($document, $oic);
        $this->assertFalse($step->fresh()->signed_for);
    }

    public function test_routing_slip_always_names_head_and_oic_signs_for_head(): void
    {
        $type = DocumentType::create(['name' => 'Routing Letter', 'abbreviation' => 'RL']);
        $sender = $this->office('Sender Two', 'S2');
        $routingOffice = $this->office('CICS', 'CICS');
        $head = $routingOffice->head;
        $head->update(['name' => 'Maria Clara', 'position' => 'Dean']);
        $oic = User::factory()->create([
            'name' => 'Elena Garcia',
            'office_id' => $routingOffice->id,
            'signature' => 'signatures/elena.png',
        ]);
        $routingOffice->update(['acting_head_id' => $oic->id]);
        $stage = $this->stage($type, $routingOffice, 'routing', 'Reviewed by', 1, true, false);
        $document = Document::create([
            'document_number' => 'S2-RL-1-2026', 'from_id' => $sender->id, 'to_id' => $routingOffice->id,
            'document_type_id' => $type->id, 'subject' => 'Routing', 'content' => 'Test',
            'created_by' => $sender->head_id, 'status' => 'Sent',
        ]);
        $component = new CreateDocument;
        $component->document_type_id = (string) $type->id;
        $component->flowStages = [$stage->load('office', 'workflowCondition')->toArray()];
        $component->signatories = [];
        $component->cf_offices = [];

        $method = new ReflectionMethod(CreateDocument::class, 'processDocumentSteps');
        $method->setAccessible(true);
        $method->invoke($component, $document);

        $step = $document->steps()->firstOrFail();
        $this->assertSame($oic->id, $step->user_id);
        $this->assertSame($head->id, $step->assigned_user_id);
        $this->assertSame('Maria Clara', $step->signatory_name);
        $this->assertSame('Dean', $step->signatory_position);

        app(DocumentWorkflowService::class)->approve($document, $oic);
        $step->refresh();
        $this->assertTrue($step->signed_for);
        $this->assertSame('signatures/elena.png', $step->signature_path);
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
        $workflowConditionId = null;
        $conditionValue = null;
        if ($condition !== 'always') {
            $workflowConditionId = WorkflowCondition::firstOrCreate(
                ['key' => 'has_budget_implications'],
                ['label' => 'Has budget implications?', 'input_type' => 'boolean', 'is_active' => true],
            )->id;
            $conditionValue = $condition === 'with_budget' ? '1' : '0';
        }

        return DocumentFlowStage::create([
            'document_type_id' => $type->id, 'office_id' => $office->id, 'stage_type' => $stageType,
            'label' => $label, 'sequence' => $sequence, 'is_required' => $required,
            'is_selectable' => $selectable, 'workflow_condition_id' => $workflowConditionId,
            'condition_operator' => 'equals', 'condition_value' => $conditionValue,
        ]);
    }
}
