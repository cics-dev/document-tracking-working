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
    public string $recipient_mode = 'office';
    public string $recipient_label = 'To';
    public string $recipient_office_key = '';
    public string $document_level = 'Inter';
    public string $number_prefix = '';
    public bool $show_thru = true;
    public bool $show_carbon_copy = true;
    public bool $requires_signatories = false;
    public bool $is_publicly_creatable = false;
    public string $content_template = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_document_flows'), 403);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('document_types', 'name')->ignore($this->editingId)],
            'abbreviation' => ['required', 'string', 'max:50', Rule::unique('document_types', 'abbreviation')->ignore($this->editingId)],
            'recipient_mode' => ['required', Rule::in(['office', 'text', 'none'])],
            'recipient_label' => ['required', 'string', 'max:50'],
            'recipient_office_key' => ['nullable', 'string', 'max:100'],
            'document_level' => ['required', 'string', 'max:50'],
            'number_prefix' => ['nullable', 'string', 'max:255'],
            'show_thru' => ['boolean'], 'show_carbon_copy' => ['boolean'],
            'requires_signatories' => ['boolean'], 'is_publicly_creatable' => ['boolean'],
            'content_template' => ['nullable', 'string'],
        ]);

        DocumentType::updateOrCreate(['id' => $this->editingId], [
            'name' => trim($validated['name']),
            'abbreviation' => strtoupper(trim($validated['abbreviation'])),
            ...collect($validated)->except(['name', 'abbreviation'])->map(fn ($value) => is_string($value) ? trim($value) : $value)->all(),
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
        foreach (['recipient_mode', 'recipient_label', 'recipient_office_key', 'document_level', 'number_prefix', 'show_thru', 'show_carbon_copy', 'requires_signatories', 'is_publicly_creatable', 'content_template'] as $field) {
            $this->{$field} = $type->{$field} ?? $this->{$field};
        }
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
        $this->reset('editingId', 'name', 'abbreviation', 'recipient_office_key', 'number_prefix', 'requires_signatories', 'is_publicly_creatable', 'content_template');
        $this->recipient_mode = 'office'; $this->recipient_label = 'To'; $this->document_level = 'Inter';
        $this->show_thru = true; $this->show_carbon_copy = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.document-types.manage-document-types', [
            'types' => DocumentType::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
