<?php

namespace App\Livewire\DocumentFlows;

use App\Models\DocumentFlowStage;
use App\Models\DocumentType;
use App\Models\Office;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageDocumentFlows extends Component
{
    public string $documentTypeId = '';
    public ?int $stageId = null;
    public string $officeId = '';
    public string $stageType = 'routing';
    public string $label = '';
    public string $description = '';
    public bool $isRequired = true;
    public bool $isSelectable = false;
    public string $condition = 'always';

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->documentTypeId = (string) (DocumentType::orderBy('name')->value('id') ?? '');
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
        $this->condition = $stage->condition;
    }

    public function updatedStageType(string $value): void
    {
        if ($value === 'signatory' && ! in_array($this->label, ['Approved by', 'Recommending Approval'], true)) {
            $this->label = 'Recommending Approval';
        }
    }

    public function save(): void
    {
        $this->authorizeAccess();
        if ($this->stageType === 'signatory') {
            $this->label = in_array($this->label, ['Approved by', 'Recommending Approval'], true)
                ? $this->label
                : 'Recommending Approval';
        }
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
            'condition' => ['required', Rule::in(['always', 'with_budget', 'without_budget'])],
        ]);

        DocumentFlowStage::updateOrCreate(['id' => $this->stageId], [
            'document_type_id' => $data['documentTypeId'], 'office_id' => $data['officeId'],
            'stage_type' => $data['stageType'], 'label' => $data['label'], 'description' => $data['description'] ?: null,
            'sequence' => $this->stageId
                ? DocumentFlowStage::findOrFail($this->stageId)->sequence
                : $this->nextInternalOrder($data['documentTypeId'], $data['stageType']),
            'is_required' => $data['isRequired'], 'is_selectable' => $data['isSelectable'], 'condition' => $data['condition'],
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
        $this->stageType = 'routing'; $this->isRequired = true; $this->isSelectable = false; $this->condition = 'always';
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

    public function render()
    {
        return view('livewire.document-flows.manage-document-flows', [
            'types' => DocumentType::orderBy('name')->get(),
            'offices' => Office::orderBy('name')->get(['id', 'name', 'abbreviation']),
            'stages' => DocumentFlowStage::with('office')->where('document_type_id', $this->documentTypeId)
                ->orderByRaw("CASE stage_type WHEN 'routing' THEN 1 WHEN 'signatory' THEN 2 ELSE 3 END")
                ->orderBy('sequence')->orderBy('id')->get(),
        ])->layout('layouts.app');
    }
}
