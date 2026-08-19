<?php

namespace App\Livewire\DocumentFlows;

use App\Models\DocumentFlowStage;
use App\Models\DocumentGenerationRule;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Role;
use App\Models\WorkflowCondition;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageDocumentFlows extends Component
{
    public string $documentTypeId = '';

    public ?int $stageId = null;

    public string $officeId = '';

    public string $stageType = '';

    public string $label = '';

    public string $description = '';

    public bool $isRequired = false;

    public bool $isSelectable = false;

    public string $workflowConditionId = '';

    public string $conditionOperator = '';

    public string $conditionValue = '';

    public string $newConditionKey = '';

    public string $newConditionLabel = '';

    public string $newConditionType = '';

    public string $newConditionOptions = '';

    public string $generationContext = '';

    public string $generationSourceTypeId = '';

    public string $generationTargetTypeId = '';

    public string $generationLabel = '';

    public string $generationStatus = 'Approved';

    public bool $generationRequiresAssignment = false;

    public array $generationRoles = [];

    public function mount(): void
    {
        $this->authorizeAccess();
    }

    public function edit(int $id): void
    {
        $stage = DocumentFlowStage::findOrFail($id);
        $this->stageId = $stage->id;
        $this->officeId = (string) $stage->office_id;
        $this->stageType = $stage->stage_type;
        $this->label = $stage->label;
        $this->description = $stage->description ?? '';
        $this->isRequired = $stage->is_required;
        $this->isSelectable = $stage->is_selectable;
        $this->workflowConditionId = (string) ($stage->workflow_condition_id ?? '');
        $this->conditionOperator = $stage->condition_operator ?? 'equals';
        $this->conditionValue = $stage->condition_value ?? '';
    }

    public function updatedWorkflowConditionId(): void
    {
        $this->conditionOperator = '';
        $this->conditionValue = '';
        $this->resetValidation(['conditionValue', 'conditionOperator']);
    }

    public function updatedStageType(string $value): void
    {
        if ($value === 'signatory' && ! in_array($this->label, ['Approved by', 'Recommending Approval'], true)) {
            $this->label = '';
        }
    }

    public function save(): void
    {
        $this->authorizeAccess();
        $data = $this->validate([
            'documentTypeId' => ['required', 'exists:document_types,id'],
            'officeId' => ['required', 'exists:offices,id'],
            'stageType' => ['required', Rule::in(['routing', 'signatory', 'action'])],
            'label' => $this->stageType === 'signatory'
                ? ['required', Rule::in(['Approved by', 'Recommending Approval'])]
                : ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'isRequired' => ['boolean'],
            'isSelectable' => ['boolean'],
            'workflowConditionId' => ['nullable', 'exists:workflow_conditions,id'],
            'conditionOperator' => $this->workflowConditionId === ''
                ? ['nullable']
                : ['required', Rule::in(['equals', 'not_equals', 'greater_than', 'less_than', 'contains'])],
            'conditionValue' => $this->conditionValueRules(),
        ]);

        DocumentFlowStage::updateOrCreate(['id' => $this->stageId], [
            'document_type_id' => $data['documentTypeId'], 'office_id' => $data['officeId'],
            'stage_type' => $data['stageType'], 'label' => $data['label'], 'description' => $data['description'] ?: null,
            'sequence' => $this->stageId
                ? DocumentFlowStage::findOrFail($this->stageId)->sequence
                : $this->nextInternalOrder($data['documentTypeId'], $data['stageType']),
            'is_required' => $data['isRequired'], 'is_selectable' => $data['isSelectable'],
            'workflow_condition_id' => $data['workflowConditionId'] !== '' ? $data['workflowConditionId'] : null,
            'condition_operator' => $data['conditionOperator'] ?: 'equals',
            'condition_value' => $data['conditionValue'] === '' ? null : $data['conditionValue'],
        ]);
        session()->flash('status', $this->stageId ? 'Flow stage updated.' : 'Flow stage added.');
        $this->resetStage();
    }

    public function delete(int $id): void
    {
        $this->authorizeAccess();
        DocumentFlowStage::where('id', $id)->where('document_type_id', $this->documentTypeId)->firstOrFail()->delete();
        session()->flash('status', 'Flow stage deleted.');
        $this->resetStage();
    }

    public function resetStage(): void
    {
        $this->reset('stageId', 'officeId', 'label', 'description');
        $this->stageType = '';
        $this->isRequired = false;
        $this->isSelectable = false;
        $this->workflowConditionId = '';
        $this->conditionOperator = '';
        $this->conditionValue = '';
        $this->resetValidation();
    }

    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_document_flows'), 403);
    }

    private function nextInternalOrder(int|string $documentTypeId, string $stageType): int
    {
        $phase = ['routing' => 1000, 'signatory' => 2000, 'action' => 3000][$stageType];
        $current = DocumentFlowStage::where('document_type_id', $documentTypeId)
            ->where('stage_type', $stageType)->max('sequence');

        return $current && $current >= $phase ? $current + 10 : $phase;
    }

    private function conditionValueRules(): array
    {
        if ($this->workflowConditionId === '') {
            return ['nullable', 'string', 'max:255'];
        }
        $condition = WorkflowCondition::find($this->workflowConditionId);
        $rules = ['required', 'string', 'max:255'];
        if ($condition?->input_type === 'number') {
            $rules[] = 'numeric';
        }
        if ($condition?->input_type === 'boolean') {
            $rules[] = Rule::in(['0', '1']);
        }
        if ($condition?->input_type === 'select') {
            $rules[] = Rule::in($condition->options ?? []);
        }

        return $rules;
    }

    public function selectedCondition(): ?WorkflowCondition
    {
        return $this->workflowConditionId === '' ? null : WorkflowCondition::find($this->workflowConditionId);
    }

    public function addCondition(): void
    {
        $this->authorizeAccess();
        $data = $this->validate([
            'newConditionKey' => ['required', 'alpha_dash', 'max:100', 'unique:workflow_conditions,key'],
            'newConditionLabel' => ['required', 'string', 'max:255'],
            'newConditionType' => ['required', Rule::in(['boolean', 'select', 'text', 'number'])],
            'newConditionOptions' => ['nullable', 'string'],
        ]);
        WorkflowCondition::create(['key' => $data['newConditionKey'], 'label' => $data['newConditionLabel'], 'input_type' => $data['newConditionType'], 'options' => collect(explode(',', $data['newConditionOptions']))->map(fn ($v) => trim($v))->filter()->values()->all() ?: null]);
        $this->reset('newConditionKey', 'newConditionLabel', 'newConditionOptions');
        $this->newConditionType = '';
        session()->flash('status', 'Workflow condition added.');
    }

    public function addGenerationRule(): void
    {
        $this->authorizeAccess();
        $data = $this->validate([
            'generationContext' => ['required', Rule::in(['internal', 'external'])],
            'generationSourceTypeId' => [$this->generationContext === 'internal' ? 'required' : 'nullable', 'exists:document_types,id'],
            'generationTargetTypeId' => ['required', 'exists:document_types,id'],
            'generationLabel' => ['required', 'string', 'max:100'],
            'generationStatus' => ['nullable', 'string', 'max:50'],
            'generationRequiresAssignment' => ['boolean'], 'generationRoles' => ['required', 'array', 'min:1'],
            'generationRoles.*' => ['exists:roles,id'],
        ]);
        $rule = DocumentGenerationRule::create([
            'source_context' => $data['generationContext'], 'source_document_type_id' => $data['generationContext'] === 'internal' ? $data['generationSourceTypeId'] : null,
            'target_document_type_id' => $data['generationTargetTypeId'], 'button_label' => $data['generationLabel'],
            'required_status' => $data['generationStatus'] ?: null, 'requires_assigned_office' => $data['generationRequiresAssignment'], 'is_active' => true,
        ]);
        $rule->roles()->sync($data['generationRoles']);
        $this->reset('generationContext', 'generationSourceTypeId', 'generationTargetTypeId', 'generationLabel', 'generationRoles');
        $this->generationRequiresAssignment = false;
        session()->flash('status', 'Generation rule added.');
    }

    public function deleteGenerationRule(int $id): void
    {
        $this->authorizeAccess();
        DocumentGenerationRule::findOrFail($id)->delete();
    }

    public function toggleGenerationRule(int $id): void
    {
        $this->authorizeAccess();
        $rule = DocumentGenerationRule::findOrFail($id);
        $rule->update(['is_active' => ! $rule->is_active]);
    }

    public function toggleCondition(int $id): void
    {
        $this->authorizeAccess();
        $condition = WorkflowCondition::findOrFail($id);
        $condition->update(['is_active' => ! $condition->is_active]);
    }

    public function render()
    {
        return view('livewire.document-flows.manage-document-flows', [
            'types' => DocumentType::orderBy('name')->get(),
            'offices' => Office::orderBy('name')->get(['id', 'name', 'abbreviation']),
            'stages' => DocumentFlowStage::with('office', 'workflowCondition')->where('document_type_id', $this->documentTypeId)
                ->orderByRaw("CASE stage_type WHEN 'routing' THEN 1 WHEN 'signatory' THEN 2 ELSE 3 END")
                ->orderBy('sequence')->orderBy('id')->get(),
            'conditions' => WorkflowCondition::orderBy('label')->get(),
            'generationRules' => DocumentGenerationRule::with('sourceType', 'targetType', 'roles')->orderBy('button_label')->get(),
            'roles' => Role::orderBy('description')->get(),
        ])->layout('layouts.app');
    }
}
