<?php

namespace App\Livewire\DocumentTypes;

use App\Models\DocumentType;
use App\Models\Office;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageDocumentTypes extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $abbreviation = '';

    public string $chip_color = '#dbeafe';

    public string $recipient_mode = '';

    public string $recipient_label = 'To';

    public string $recipient_office_id = '';

    public string $document_level = '';

    public string $number_prefix = '';

    public bool $show_thru = false;

    public bool $show_carbon_copy = false;

    public bool $allow_attachments = false;

    public bool $requires_signatories = false;

    public bool $is_publicly_creatable = false;

    public string $content_template = '';

    public string $print_layout = '';

    public string $sender_signature_policy = '';

    public string $approver_display_mode = '';

    public bool $allow_oic_signature = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_document_flows'), 403);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('document_types', 'name')->ignore($this->editingId)],
            'abbreviation' => ['required', 'string', 'max:50', Rule::unique('document_types', 'abbreviation')->ignore($this->editingId)],
            'chip_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'recipient_mode' => ['required', Rule::in(['office', 'text', 'none'])],
            'recipient_label' => ['required', 'string', 'max:50'],
            'recipient_office_id' => ['nullable', 'exists:offices,id'],
            'document_level' => ['required', Rule::in(['Inter', 'Intra'])],
            'number_prefix' => ['nullable', 'string', 'max:255'],
            'show_thru' => ['boolean'], 'show_carbon_copy' => ['boolean'], 'allow_attachments' => ['boolean'],
            'requires_signatories' => ['boolean'], 'is_publicly_creatable' => ['boolean'],
            'content_template' => ['nullable', 'string'],
            'print_layout' => ['required', Rule::in(['memorandum', 'letter', 'indorsement', 'special_order'])],
            'sender_signature_policy' => ['required', Rule::in(['always', 'approved', 'never'])],
            'approver_display_mode' => ['required', Rule::in(['action_box', 'labeled', 'signature_only', 'hidden'])],
            'allow_oic_signature' => ['boolean'],
        ]);

        $behavior = collect($validated)->except(['name', 'abbreviation'])->map(fn ($value) => is_string($value) ? trim($value) : $value)->all();
        $behavior['recipient_office_id'] = $validated['recipient_mode'] === 'office' && filled($validated['recipient_office_id'] ?? null)
            ? (int) $validated['recipient_office_id'] : null;
        foreach (['number_prefix', 'content_template'] as $nullableField) {
            $behavior[$nullableField] = filled($behavior[$nullableField] ?? null) ? $behavior[$nullableField] : null;
        }

        DocumentType::updateOrCreate(['id' => $this->editingId], [
            'name' => trim($validated['name']),
            'abbreviation' => strtoupper(trim($validated['abbreviation'])),
            ...$behavior,
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
        $this->chip_color = $type->chip_color;
        foreach (['recipient_mode', 'recipient_label', 'recipient_office_id', 'document_level', 'number_prefix', 'show_thru', 'show_carbon_copy', 'allow_attachments', 'requires_signatories', 'is_publicly_creatable', 'content_template', 'print_layout', 'sender_signature_policy', 'approver_display_mode', 'allow_oic_signature'] as $field) {
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
        $this->reset('editingId', 'name', 'abbreviation', 'recipient_office_id', 'number_prefix', 'requires_signatories', 'is_publicly_creatable', 'content_template');
        $this->recipient_mode = '';
        $this->recipient_label = 'To';
        $this->document_level = '';
        $this->chip_color = '#dbeafe';
        $this->show_thru = false;
        $this->show_carbon_copy = false;
        $this->allow_attachments = false;
        $this->print_layout = '';
        $this->sender_signature_policy = '';
        $this->approver_display_mode = '';
        $this->allow_oic_signature = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.document-types.manage-document-types', [
            'types' => DocumentType::orderBy('name')->get(),
            'recipientOffices' => Office::orderBy('name')->get(['id', 'name', 'abbreviation']),
        ])->layout('layouts.app');
    }
}
