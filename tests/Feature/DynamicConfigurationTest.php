<?php

namespace Tests\Feature;

use App\Livewire\Documents\CreateDocument;
use App\Models\Document;
use App\Models\DocumentFlowStage;
use App\Models\DocumentGenerationRule;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowCondition;
use App\Services\DocumentGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class DynamicConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_condition_can_control_a_stage_without_new_code(): void
    {
        $type = DocumentType::create(['name' => 'Indorsement Letter', 'abbreviation' => 'IL']);
        $sender = $this->office('Sender', 'S');
        $legal = $this->office('Legal', 'L');
        $condition = WorkflowCondition::create(['key' => 'risk_score', 'label' => 'Risk score', 'input_type' => 'number']);
        DocumentFlowStage::create([
            'document_type_id' => $type->id, 'office_id' => $legal->id, 'stage_type' => 'routing',
            'label' => 'Legal review', 'sequence' => 10, 'is_required' => true, 'is_selectable' => false,
            'workflow_condition_id' => $condition->id,
            'condition_operator' => 'greater_than', 'condition_value' => '50',
        ]);
        $document = Document::create(['document_number' => 'IL-1', 'from_id' => $sender->id, 'document_type_id' => $type->id, 'subject' => 'Test', 'content' => 'Test', 'created_by' => $sender->head_id, 'status' => 'Sent']);

        $component = new CreateDocument;
        $component->document_type = 'IL';
        $component->document_type_id = (string) $type->id;
        $component->flowStages = DocumentFlowStage::where('document_type_id', $type->id)->get()->toArray();
        $component->conditionValues = [(string) $condition->id => 75];
        $component->cf_offices = [];
        $method = new ReflectionMethod(CreateDocument::class, 'processDocumentSteps');
        $method->setAccessible(true);
        $method->invoke($component, $document);

        $this->assertDatabaseHas('document_steps', ['document_id' => $document->id, 'office_id' => $legal->id]);
    }

    public function test_required_stage_is_included_only_when_its_dynamic_boolean_condition_is_checked(): void
    {
        $type = DocumentType::create(['name' => 'Request Letter', 'abbreviation' => 'RLM']);
        $sender = $this->office('Sender Two', 'S2');
        $budget = $this->office('Budget Office', 'BO');
        $condition = WorkflowCondition::create(['key' => 'has_budget', 'label' => 'Has budget?', 'input_type' => 'boolean']);
        DocumentFlowStage::create([
            'document_type_id' => $type->id, 'office_id' => $budget->id, 'stage_type' => 'routing',
            'label' => 'Budget Review', 'sequence' => 10, 'is_required' => true, 'is_selectable' => false,
            'workflow_condition_id' => $condition->id,
            'condition_operator' => 'equals', 'condition_value' => '1',
        ]);

        foreach ([false, true] as $checked) {
            $document = Document::create(['document_number' => 'RLM-'.(int) $checked, 'from_id' => $sender->id, 'document_type_id' => $type->id, 'subject' => 'Test', 'content' => 'Test', 'created_by' => $sender->head_id, 'status' => 'Sent']);
            $component = new CreateDocument;
            $component->document_type = 'RLM';
            $component->document_type_id = (string) $type->id;
            $component->flowStages = DocumentFlowStage::where('document_type_id', $type->id)->get()->toArray();
            $component->conditionValues = [(string) $condition->id => $checked];
            $component->cf_offices = [];
            $method = new ReflectionMethod(CreateDocument::class, 'processDocumentSteps');
            $method->setAccessible(true);
            $method->invoke($component, $document);
            $this->assertSame($checked, $document->steps()->where('office_id', $budget->id)->exists());
        }
    }

    public function test_required_signatory_is_included_only_when_its_boolean_condition_is_checked(): void
    {
        $type = DocumentType::create(['name' => 'Budget Request Letter', 'abbreviation' => 'BRL']);
        $sender = $this->office('Requesting Office', 'REQ');
        $vpaf = $this->office('Vice President for Administration and Finance', 'VPAF');
        $condition = WorkflowCondition::create([
            'key' => 'has_budget_implications',
            'label' => 'Has budget implications?',
            'input_type' => 'boolean',
        ]);
        DocumentFlowStage::create([
            'document_type_id' => $type->id,
            'office_id' => $vpaf->id,
            'stage_type' => 'signatory',
            'label' => 'Recommending Approval',
            'sequence' => 10,
            'is_required' => true,
            'is_selectable' => false,
            'workflow_condition_id' => $condition->id,
            'condition_operator' => 'equals',
            'condition_value' => '1',
        ]);

        foreach ([false, true] as $hasBudgetImplications) {
            $document = Document::create([
                'document_number' => 'BRL-'.(int) $hasBudgetImplications,
                'from_id' => $sender->id,
                'document_type_id' => $type->id,
                'subject' => 'Conditional VPAF approval',
                'content' => 'Test',
                'created_by' => $sender->head_id,
                'status' => 'Sent',
            ]);
            $component = new CreateDocument;
            $component->document_type = 'BRL';
            $component->document_type_id = (string) $type->id;
            $component->flowStages = DocumentFlowStage::with('workflowCondition')
                ->where('document_type_id', $type->id)->get()->toArray();
            $component->conditionValues = [(string) $condition->id => $hasBudgetImplications];
            $component->cf_offices = [];

            $method = new ReflectionMethod(CreateDocument::class, 'processDocumentSteps');
            $method->setAccessible(true);
            $method->invoke($component, $document);

            $this->assertSame(
                $hasBudgetImplications,
                $document->steps()->where('office_id', $vpaf->id)->exists()
            );
        }
    }

    public function test_checking_a_condition_checks_and_locks_its_required_routing_stage(): void
    {
        $type = DocumentType::create(['name' => 'Request', 'abbreviation' => 'REQ']);
        $budget = $this->office('Budget Review Office', 'BRO');
        $condition = WorkflowCondition::create(['key' => 'budget_ui', 'label' => 'Has budget?', 'input_type' => 'boolean']);
        $stage = DocumentFlowStage::create([
            'document_type_id' => $type->id, 'office_id' => $budget->id, 'stage_type' => 'routing',
            'label' => 'Budget Office Review', 'sequence' => 10, 'is_required' => true, 'is_selectable' => true,
            'workflow_condition_id' => $condition->id,
            'condition_operator' => 'equals', 'condition_value' => '1',
        ])->load('workflowCondition');
        $component = new CreateDocument;
        $component->flowStages = [$stage->toArray()];
        $component->conditionValues = [(string) $condition->id => true];
        $component->updatedConditionValues();

        $this->assertTrue($component->selectedFlowStages[(string) $stage->id]);
        $this->assertTrue($component->conditionLocksStage($stage->toArray()));

        $component->conditionValues[(string) $condition->id] = false;
        $component->updatedConditionValues();
        $this->assertFalse($component->conditionLocksStage($stage->toArray()));
        $this->assertFalse($component->selectedFlowStages[(string) $stage->id]);
    }

    public function test_new_generation_mapping_appears_for_an_allowed_role_without_button_code(): void
    {
        $sourceType = DocumentType::create(['name' => 'Indorsement Letter', 'abbreviation' => 'IL']);
        $targetType = DocumentType::create(['name' => 'External Reply', 'abbreviation' => 'ECLR']);
        $role = Role::create(['role' => 'generator', 'description' => 'Generator']);
        $permission = Permission::where('key', 'send_documents')->firstOrFail();
        $role->permissions()->attach($permission);
        $office = $this->office('Generator Office', 'GO');
        $user = User::factory()->create(['office_id' => $office->id, 'role_id' => $role->id]);
        $office->update(['head_id' => $user->id]);
        $document = Document::create(['document_number' => 'IL-2', 'from_id' => $office->id, 'to_id' => $office->id, 'document_type_id' => $sourceType->id, 'subject' => 'Test', 'content' => 'Test', 'created_by' => $user->id, 'status' => 'Approved']);
        $document->steps()->create(['user_id' => $user->id, 'office_id' => $office->id, 'step_type' => 'action', 'step_label' => 'Generate', 'sequence' => 1, 'status' => 'Pending']);
        $rule = DocumentGenerationRule::create(['source_context' => 'internal', 'source_document_type_id' => $sourceType->id, 'target_document_type_id' => $targetType->id, 'button_label' => 'Generate ECLR', 'required_status' => 'Approved', 'requires_assigned_office' => true]);
        $rule->roles()->attach($role);

        $available = app(DocumentGenerationService::class)->availableForInternal($document->fresh(['steps']), $user);
        $this->assertSame(['Generate ECLR'], $available->pluck('button_label')->all());
        $this->assertSame('ECLR', app(DocumentGenerationService::class)->redirectData($rule->load('targetType'), $document->fresh(['steps']), $user)['document_type']);

        $this->actingAs($user);
        $component = new CreateDocument;
        $component->original_document_id = $document->id;
        $component->document_type_id = (string) $targetType->id;
        $method = new ReflectionMethod(CreateDocument::class, 'ensureDocumentTypeAllowed');
        $method->setAccessible(true);
        $method->invoke($component);
        $this->assertTrue(true);
    }

    private function office(string $name, string $abbreviation): Office
    {
        $office = Office::create(compact('name', 'abbreviation') + ['office_type' => 'ADMIN']);
        $head = User::factory()->create(['office_id' => $office->id]);
        $office->update(['head_id' => $head->id]);

        return $office->fresh();
    }
}
