<?php

namespace App\Livewire\DocumentTypes;

use App\Models\DocumentType;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageDocumentTypes extends Component
{
    public ?int $editingId = null;
    public string $name = '';
    public string $abbreviation = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_document_flows'), 403);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('document_types', 'name')->ignore($this->editingId)],
            'abbreviation' => ['required', 'string', 'max:50', Rule::unique('document_types', 'abbreviation')->ignore($this->editingId)],
        ]);

        DocumentType::updateOrCreate(['id' => $this->editingId], [
            'name' => trim($validated['name']),
            'abbreviation' => strtoupper(trim($validated['abbreviation'])),
        ]);

        $this->resetForm();
        session()->flash('status', 'Document type saved.');
    }

    public function edit(int $id): void
    {
        $type = DocumentType::findOrFail($id);
        $this->editingId = $type->id;
        $this->name = $type->name;
        $this->abbreviation = $type->abbreviation;
    }

    public function delete(int $id): void
    {
        try {
            DocumentType::findOrFail($id)->delete();
            session()->flash('status', 'Document type deleted.');
        } catch (QueryException) {
            $this->addError('delete', 'This document type is already in use and cannot be deleted.');
        }
    }

    public function resetForm(): void
    {
        $this->reset('editingId', 'name', 'abbreviation');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.document-types.manage-document-types', [
            'types' => DocumentType::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
