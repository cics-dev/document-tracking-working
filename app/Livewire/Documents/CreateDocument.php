<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\UserController;
use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Models\DocumentFlowStage;
use App\Models\DocumentGenerationRule;
use App\Models\DocumentType;
use App\Models\ExternalDocument;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateDocument extends Component
{
    use WithFileUploads;

    #[Validate(['attachments.*' => 'file|max:102400|mimes:pdf,doc,docx,jpg,jpeg,png,gif,webp'])]
    public $attachments = [];

    public $existingAttachments = [];

    public $selectedAttachment;

    public $attachmentPreviewUrl;

    public $original_document_number;

    public $revision_document_number;

    public $original_document_id = null;

    public $external_document_id = null;

    public $office_type = '';

    public $document_type = '';

    public $document_type_id = '';

    public $document_to_id = '';

    public $document_to_text = '';

    public $document_from_id = '';

    public $thru = '';

    public $subject = '';

    public $content = '';

    public $attachment = '';

    public $signatories = [];

    public $users = [];

    public $types = [];

    public $offices = [];

    public $allOffices = [];

    public $cf_offices = [];

    public $selected_cf_office = '';

    public array $flowStages = [];

    public array $selectedFlowStages = [];

    public array $conditionValues = [];

    public $readyToLoad = false;

    public $redirect_mode = null;

    public $manual_document_number = null;

    public $is_manual_document_number = false;

    public function mount($number = null, $draft_id = null)
    {
        $isExternalResponse = (bool) session('redirect_data.external_document_id');
        abort_unless(
            Auth::user()->hasAccess('send_documents') || ($isExternalResponse && Auth::user()->hasAccess('send_external_documents')),
            403,
            'You do not have permission to send documents.'
        );
        $this->redirect_mode = $number ? 'revision' : ($draft_id ? 'edit' : null);
        $this->office_type = Auth::user()->office->office_type;

        $this->users = app(UserController::class)->index(false);
        $this->types = app(DocumentTypeController::class)->index(Auth::user());
        $generatedTypeId = session('redirect_data.document_type_id');
        if ($generatedTypeId && ! collect($this->types)->contains('id', (int) $generatedTypeId)) {
            $generatedType = DocumentType::find($generatedTypeId);
            if ($generatedType) {
                $this->types->push($generatedType);
            }
        }
        $officesData = app(OfficeController::class)->index(Auth::user()->office->office_type, false);
        $this->offices = collect($officesData)->keyBy('id');
        $this->allOffices = Office::with('head', 'actingHead')->orderBy('name')->get()->keyBy('id');

        $loadedExistingDocument = $this->handleSessionData();
        if (! $loadedExistingDocument) {
            $this->handlePassedData($number, $draft_id);
        }

        if (! $loadedExistingDocument && ! $this->redirect_mode && request()->string('level')->lower()->value() === 'intra') {
            $intraType = collect($this->types)
                ->where('document_level', 'Intra')
                ->sortBy(fn (DocumentType $type) => strcasecmp($type->abbreviation, 'Intra') === 0 ? 0 : 1)
                ->first();

            if ($intraType) {
                $this->document_type_id = (string) $intraType->id;
                $this->handleUpdateDocumentType();
            }
        }
    }

    public function loadInitialContent()
    {
        $this->readyToLoad = true;
        if ($this->selectedType()?->content_template) {
            $this->updateContentWithSubject();
        }
    }

    public function render()
    {
        $selectedDocumentType = $this->selectedType();
        $flowConditions = collect($this->flowStages)
            ->pluck('workflow_condition')
            ->filter(fn ($condition) => $condition['is_active'] ?? false)
            ->unique('id')
            ->values();
        $cfOfficeRecords = collect($this->cf_offices)
            ->map(fn ($officeId) => $this->offices[$officeId] ?? null)
            ->filter()
            ->values();

        $headIsTemporarilyRelieved = $this->headIsTemporarilyRelieved();

        return view('livewire.documents.create-document', compact('selectedDocumentType', 'flowConditions', 'cfOfficeRecords', 'headIsTemporarilyRelieved'))
            ->layout('layouts.app');
    }

    public function signatoryOfficeOptions(array $signatory): array
    {
        $configuredStages = collect($this->flowStages)->where('stage_type', 'signatory')
            ->filter(fn ($stage) => strcasecmp($stage['label'], $signatory['role'] ?? '') === 0);
        if ($configuredStages->isEmpty()) {
            return collect($this->allOffices)->map(fn ($office) => [
                'value' => (string) $office->id, 'label' => $office->name, 'search' => $office->abbreviation,
            ])->values()->all();
        }

        $officeIds = $configuredStages->pluck('office_id');

        return collect($this->allOffices)->whereIn('id', $officeIds)->map(fn ($office) => [
            'value' => (string) $office->id, 'label' => $office->name, 'search' => $office->abbreviation,
        ])->values()->all();
    }

    public function configuredSignatoryDescription(array $signatory): ?array
    {
        return collect($this->flowStages)->first(fn ($stage) => $stage['stage_type'] === 'signatory'
            && (int) $stage['office_id'] === (int) ($signatory['office_id'] ?? 0)
            && strcasecmp($stage['label'], $signatory['role'] ?? '') === 0
            && ! empty($stage['description'])
        );
    }

    public function handleUpdateDocumentType()
    {
        $typeObj = collect($this->types)->firstWhere('id', $this->document_type_id);
        $this->document_type = $typeObj?->abbreviation ?? '';

        $this->signatories = [];
        $this->flowStages = DocumentFlowStage::with('office', 'workflowCondition')
            ->where('document_type_id', $this->document_type_id)
            ->orderBy('sequence')->orderBy('id')->get()->toArray();
        $this->conditionValues = collect($this->flowStages)->pluck('workflow_condition')->filter(fn ($condition) => $condition['is_active'] ?? false)->unique('id')->mapWithKeys(fn ($condition) => [(string) $condition['id'] => $condition['input_type'] === 'boolean' ? false : ''])->all();
        $this->selectedFlowStages = collect($this->flowStages)
            ->filter(fn ($stage) => $stage['is_required'] && (empty($stage['workflow_condition_id']) || $this->flowConditionArrayMatches($stage)))
            ->mapWithKeys(fn ($stage) => [(string) $stage['id'] => true])->all();
        $this->syncRequiredConfiguredSignatories();

        $type = $this->selectedType();
        if (! $type?->show_carbon_copy) {
            $this->cf_offices = [];
            $this->selected_cf_office = '';
        }
        if (! $type?->allow_attachments) {
            $this->attachments = [];
        }
        if ($type?->recipient_mode === 'office' && $type->recipient_office_id) {
            $recipientOffice = Office::with('head', 'actingHead')->find($type->recipient_office_id);
            $this->document_to_id = $recipientOffice?->id;
            $this->document_to_text = null;

        } elseif ($type?->recipient_mode === 'text') {
            $this->document_to_id = null;
        } elseif ($type?->recipient_mode === 'none') {
            $this->document_to_id = null;
            $this->document_to_text = null;
        }
    }

    public function selectedType(): ?DocumentType
    {
        $type = collect($this->types)->firstWhere('id', (int) $this->document_type_id);

        return $type instanceof DocumentType ? $type : ($this->document_type_id ? DocumentType::find($this->document_type_id) : null);
    }

    public function isIntraDocument(): bool
    {
        return ($this->selectedType()?->document_level ?? 'Inter') === 'Intra';
    }

    public function updatedConditionValues(): void
    {
        foreach ($this->flowStages as $stage) {
            if (($stage['is_required'] ?? false) && ! empty($stage['workflow_condition_id'])) {
                $this->selectedFlowStages[(string) $stage['id']] = $this->flowConditionArrayMatches($stage);
            }
        }

        $this->syncRequiredConfiguredSignatories();
    }

    private function syncRequiredConfiguredSignatories(): void
    {
        if ($this->isIntraDocument()) {
            $this->signatories = [];

            return;
        }

        $manualSignatories = collect($this->signatories)
            ->reject(fn ($signatory) => (bool) ($signatory['locked'] ?? false));

        $requiredSignatories = collect($this->flowStages)
            ->where('stage_type', 'signatory')
            ->where('is_required', true)
            ->filter(fn ($stage) => empty($stage['workflow_condition_id'])
                || ! ($stage['workflow_condition']['is_active'] ?? false)
                || $this->flowConditionArrayMatches($stage))
            ->map(fn ($stage) => [
                'role' => $stage['label'],
                'role_type' => $stage['label'],
                'office_id' => $stage['office_id'],
                'locked' => true,
            ]);

        $this->signatories = $manualSignatories
            ->concat($requiredSignatories)
            ->values()
            ->all();
    }

    public function conditionLocksStage(array $stage): bool
    {
        return (bool) ($stage['is_required'] ?? false)
            && ! empty($stage['workflow_condition_id'])
            && $this->flowConditionArrayMatches($stage);
    }

    public function addCfOffice()
    {
        if (! $this->selectedType()?->show_carbon_copy) {
            return;
        }
        $officeId = (int) $this->selected_cf_office;
        if (! $officeId || ! collect($this->allOffices)->contains('id', $officeId)) {
            return;
        }
        if (! in_array($officeId, array_map('intval', $this->cf_offices), true)) {
            $this->cf_offices[] = $officeId;
        }
        $this->selected_cf_office = '';
    }

    public function removeCfOffice($officeId)
    {
        $this->cf_offices = array_values(array_filter($this->cf_offices, fn ($selected) => (int) $selected !== (int) $officeId));
    }

    public function availableCfOfficeOptions(): array
    {
        $selected = array_map('intval', $this->cf_offices);

        return collect($this->allOffices)->reject(fn ($office) => in_array((int) $office->id, $selected, true))
            ->map(fn ($office) => [
                'value' => (string) $office->id,
                'label' => $office->name.($office->abbreviation ? " ({$office->abbreviation})" : ''),
                'search' => trim($office->name.' '.$office->abbreviation),
            ])->values()->all();
    }

    public function addSignatory($data = null)
    {
        $newSignatory = $data ?? ['role' => '', 'role_type' => '', 'office_id' => '', 'locked' => false];
        if (! array_key_exists('role_type', $newSignatory)) {
            $newSignatory['role_type'] = in_array($newSignatory['role'] ?? '', ['Reviewed by', 'Recommending Approval'], true)
                ? $newSignatory['role']
                : 'custom';
        }
        $lastIndex = count($this->signatories) - 1;

        if ($lastIndex >= 0 && ($this->signatories[$lastIndex]['locked'] ?? false)) {
            array_splice($this->signatories, $lastIndex, 0, [$newSignatory]);
        } else {
            $this->signatories[] = $newSignatory;
        }
    }

    public function updatedSignatories($value, string $key): void
    {
        if (! str_ends_with($key, '.role_type')) {
            return;
        }

        $index = (int) strstr($key, '.', true);
        if (! isset($this->signatories[$index]) || ($this->signatories[$index]['locked'] ?? false)) {
            return;
        }

        if (in_array($value, ['Reviewed by', 'Recommending Approval'], true)) {
            $this->signatories[$index]['role'] = $value;
        } elseif ($value === 'custom') {
            $this->signatories[$index]['role'] = '';
        }
    }

    public function removeSignatory($index)
    {
        if ($this->signatories[$index]['locked'] ?? false) {
            return;
        }

        unset($this->signatories[$index]);
        $this->signatories = array_values($this->signatories);
    }

    public function updateContentWithTo()
    {
        if ($this->selectedType()?->content_template) {
            $this->formatConfiguredContent();
        }
    }

    public function updateContentWithSubject()
    {
        if ($this->selectedType()?->content_template) {
            $this->formatConfiguredContent();
        }
    }

    private function formatConfiguredContent()
    {
        $html = strtr($this->selectedType()->content_template, [
            '{TO}' => strtoupper((string) $this->document_to_text),
            '{SUBJECT}' => strtoupper((string) $this->subject),
        ]);
        $this->dispatch('update-quill', ['content' => $html]);
    }

    public function previewDocument()
    {
        $fromOffice = $this->document_from_id ? Office::with('head', 'actingHead')->find($this->document_from_id) : Auth::user()->office->loadMissing('head', 'actingHead');
        $fromUser = $fromOffice ? ($this->documentTypeAssignee($fromOffice) ?? Auth::user()) : Auth::user();
        $toName = 'N/A';
        $toPosition = 'N/A';

        if ($this->selectedType()?->recipient_mode === 'office' && $this->document_to_id) {
            $toOffice = $this->offices[$this->document_to_id] ?? null;
            if ($toOffice) {
                $recipient = $this->allOffices[$this->document_to_id]?->workflow_assignee;
                $toName = $recipient?->name ?? 'N/A';
                $pos = $this->allOffices[$this->document_to_id]?->workflowAssigneePosition() ?? 'N/A';
                $toPosition = $this->allOffices[$this->document_to_id]?->qualifyPosition($pos) ?? $pos;
            }
        } else {
            $toName = $this->document_to_text;
        }

        $fromName = $fromUser->name.($fromUser->profile?->titles ? ', '.$fromUser->profile->titles : '');
        $fromPosition = $fromOffice?->workflowAssigneePosition() ?? $fromUser->position ?? 'N/A';
        $fromPosition = $fromOffice?->qualifyPosition($fromPosition) ?? $fromPosition;

        $typeObj = collect($this->types)->firstWhere('id', $this->document_type_id);
        $docTypeAbbr = $typeObj['abbreviation'] ?? 'N/A';
        $officePart = Auth::user()->office->abbreviation;

        if (Auth::user()->office->office_type) {
            $officePart .= '('.Auth::user()->office->office_type.')';
        }

        $docNumber = $this->documentNumberPrefix($typeObj, $officePart).'-_____-'.date('Y');

        $signatoriesData = collect($this->signatories)->map(function ($sig) {
            $office = $this->allOffices[$sig['office_id']] ?? null;

            return [
                'role' => $sig['role'],
                'user_name' => $office?->workflow_assignee?->name ?? '',
                'position' => $office?->workflowAssigneePosition() ?? '',
                'signature' => null,
                'signed_for' => false,
                'signed' => null,
            ];
        });

        $cfsData = collect($this->cf_offices)->map(function ($cfId) {
            return [
                'name' => $this->offices[$cfId]['name'] ?? 'Unnamed',
                'office' => $this->offices[$cfId]['name'] ?? 'Unnamed',
            ];
        });

        $query = [
            'action' => 'preview',
            'subject' => $this->subject,
            'content' => $this->content,
            'thru' => $this->thru,
            'toName' => $toName,
            'toPosition' => $toPosition,
            'fromName' => $fromName,
            'fromSignature' => $fromUser->signature,
            'fromSignedFor' => false,
            'office_logo' => $fromUser->office->office_logo,
            'fromPosition' => $fromPosition,
            'issuingOfficeName' => $fromOffice?->name,
            'documentType' => $typeObj['name'] ?? 'N/A',
            'printLayout' => $typeObj['print_layout'] ?? 'memorandum',
            'senderSignaturePolicy' => $typeObj['sender_signature_policy'] ?? 'approved',
            'approverDisplayMode' => $typeObj['approver_display_mode'] ?? 'labeled',
            'documentNumber' => $docNumber,
            'unit' => Auth::user()->office->abbreviation,
            'signatories' => $signatoriesData->toJson(),
            'cfs' => $cfsData->toJson(),
            'attachment' => $this->attachment,
        ];

        $key = uniqid();
        session([$key => $query]);

        $this->dispatch('open-preview-tab', ['url' => '/document/preview?'.$key]);
    }

    public function submitDocument($action)
    {
        $isSend = $action === 'send';
        $status = $isSend ? 'Sent' : 'Draft';

        if ($isSend && ($this->selectedType()?->allow_oic_signature ?? true) && $this->headIsTemporarilyRelieved()) {
            throw ValidationException::withMessages([
                'document' => 'This office currently has an OIC. The designated head may prepare and preview drafts, but only the OIC may send documents.',
            ]);
        }

        $this->ensureDocumentTypeAllowed();
        if ($isSend) {
            $this->validateForSend();
        } else {
            $this->validate([
                'document_type_id' => 'required',
                'subject' => 'required|max:255',
            ]);
        }

        $fromOffice = $this->document_from_id
            ? Office::with('head', 'actingHead')->find($this->document_from_id)
            : Auth::user()->office->loadMissing('head', 'actingHead');
        $fromUser = $fromOffice ? ($this->documentTypeAssignee($fromOffice) ?? Auth::user()) : Auth::user();
        $docNumber = null;

        if ($isSend && ! $this->is_manual_document_number && empty($this->document_number)) {
            $docNumber = $this->generateDocumentNumber($fromUser->office->id);
        } else {
            $docNumber = $this->manual_document_number;
        }

        $this->ensureGenerationIsAllowed();

        $isRevision = ! empty($this->revision_document_number);
        $isGenerated = ! empty($this->original_document_id) && ! $isRevision
            && DocumentGenerationRule::where('source_document_type_id', Document::find($this->original_document_id)?->document_type_id)
                ->where('target_document_type_id', $this->document_type_id)->where('is_active', true)->exists();

        $data = [
            'from_id' => $fromUser->office->id,
            'from_user_id' => $fromUser->id,
            'from_name' => $isSend ? $fromUser->name : null,
            'from_position' => $isSend ? ($fromUser->office->workflowAssigneePosition() ?? $fromUser->position) : null,
            'to_id' => $this->selectedType()?->recipient_mode === 'office' ? $this->document_to_id : null,
            'to_name' => $isSend && $this->selectedType()?->recipient_mode === 'office' ? Office::find($this->document_to_id)?->workflow_assignee?->name : null,
            'to_position' => $isSend && $this->selectedType()?->recipient_mode === 'office' ? Office::find($this->document_to_id)?->workflowAssigneePosition() : null,
            'to_text' => $this->selectedType()?->recipient_mode === 'text' ? $this->document_to_text : null,
            'document_type_id' => $this->document_type_id,
            'document_number' => $this->revision_document_number ?? $docNumber,
            'subject' => $this->subject,
            'thru' => $this->thru,
            'content' => $this->content,
            'created_by' => Auth::id(),
            'status' => $status,
            'date_sent' => now(),
            'document_level' => $this->selectedType()?->document_level ?? 'Inter',
            'is_revision' => $isRevision,
            'original_document_id' => $isRevision ? $this->original_document_id : ($isGenerated ? $this->original_document_id : null),
        ];

        $existingDraft = Document::where('id', $this->original_document_id)->where('status', 'Draft')->first();

        $document = DB::transaction(function () use ($existingDraft, $data, $isSend) {
            $document = $existingDraft ?: new Document;
            $document->fill($data)->save();

            if ($isSend) {
                $document->attachments()->delete();
                $document->steps()->delete();
                $document->cfs()->delete();
                $this->processAttachments($document);
                $this->processSpecialDocumentTypes($document);
                $this->processDocumentSteps($document);
            }

            return $document;
        });

        if ($isSend) {
            $document->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'Sent',
                'description' => 'Document Sent',
            ]);
        }

        session()->flash('message', $isSend ? 'Document successfully sent.' : 'Document saved as draft.');

        return redirect()->route('documents.list-documents', ['mode' => 'Sent']);
    }

    private function ensureGenerationIsAllowed(): void
    {
        if (! $this->original_document_id) {
            return;
        }

        $source = $this->original_document_id ? Document::find($this->original_document_id) : null;
        $rule = $source ? DocumentGenerationRule::where('source_context', 'internal')
            ->where('source_document_type_id', $source->document_type_id)
            ->where('target_document_type_id', $this->document_type_id)->where('is_active', true)->first() : null;
        if (! $rule) {
            return;
        }
        if (! $rule->roles()->whereKey(Auth::user()->effectiveRoleId())->exists()) {
            throw ValidationException::withMessages([
                'document_type_id' => 'This generation action is not assigned to your role.',
            ]);
        }
        if ($rule->required_status && $source->status !== $rule->required_status) {
            throw ValidationException::withMessages([
                'document_type_id' => "This document may only be generated when its source is {$rule->required_status}.",
            ]);
        }

        $generationStep = $source->steps()
            ->with('office.actingHead', 'office.head')
            ->where('step_type', 'action')
            ->where('status', 'Pending')
            ->first();
        $canGenerate = $generationStep?->isAssignedTo(Auth::user());

        if ($rule->requires_assigned_office && ! $canGenerate) {
            throw ValidationException::withMessages([
                'document_type_id' => 'Only the office assigned to the generation step may create this document.',
            ]);
        }
    }

    protected function validateForSend()
    {
        $rules = [
            'document_type_id' => 'required',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'manual_document_number' => $this->is_manual_document_number ? 'required|unique:documents,document_number' : 'nullable',
            'cf_offices' => $this->selectedType()?->show_carbon_copy ? ['array'] : ['prohibited'],
            'cf_offices.*' => ['distinct', 'exists:offices,id'],
            'attachments' => $this->selectedType()?->allow_attachments ? ['array'] : ['prohibited'],
        ];

        if ($this->selectedType()?->recipient_mode === 'office') {
            $rules['document_to_id'] = 'required';
        } elseif ($this->selectedType()?->recipient_mode === 'text') {
            $rules['document_to_text'] = 'required';
        }

        if (! $this->isIntraDocument() && ($this->selectedType()?->requires_signatories || ! empty($this->signatories))) {
            $rules['signatories'] = $this->selectedType()?->requires_signatories ? 'required|array|min:1' : 'array';
            $rules['signatories.*.role'] = 'required';
            $rules['signatories.*.office_id'] = 'required';
        }

        $this->validate($rules, [
            'signatories.required' => 'At least one signatory is required.',
            'signatories.min' => 'At least one signatory is required.',
            'signatories.*.role.required' => 'Role is required.',
            'signatories.*.office_id.required' => 'Signatory is required.',
        ]);

    }

    private function ensureDocumentTypeAllowed(): void
    {
        $isPublic = $this->selectedType()?->is_publicly_creatable ?? false;
        $allowed = DB::table('role_document_types')
            ->where('role_id', Auth::user()->effectiveRoleId())
            ->where('document_type_id', $this->document_type_id)
            ->where('is_allowed', true)
            ->exists();

        $source = $this->original_document_id ? Document::find($this->original_document_id) : null;
        $allowedByGenerationRule = $source && DocumentGenerationRule::where('source_context', 'internal')
            ->where('source_document_type_id', $source->document_type_id)
            ->where('target_document_type_id', $this->document_type_id)
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->whereKey(Auth::user()->effectiveRoleId()))
            ->exists();

        if (! $isPublic && ! $allowed && ! $allowedByGenerationRule) {
            throw ValidationException::withMessages([
                'document_type_id' => 'You are not authorized to create this document type.',
            ]);
        }
    }

    private function generateDocumentNumber($from_id)
    {
        $typeObj = collect($this->types)->firstWhere('id', $this->document_type_id);

        $documents = Document::where('from_id', $from_id)
            ->where('document_type_id', $this->document_type_id)
            ->where('status', '!=', 'Draft')
            ->whereYear('created_at', date('Y'))
            ->pluck('document_number');

        $lastNumber = 0;

        foreach ($documents as $docNumber) {
            if (empty($docNumber)) {
                continue;
            }

            $parts = explode('-', trim($docNumber));
            $count = count($parts);

            for ($i = $count - 2; $i >= 0; $i--) {
                $part = trim($parts[$i]);

                if (preg_match('/\d+/', $part, $matches)) {
                    $num = (int) $matches[0];
                    if ($num > $lastNumber) {
                        $lastNumber = $num;
                    }
                    break;
                }
            }
        }

        $officePart = Auth::user()->office->abbreviation;
        if (Auth::user()->office->office_type) {
            $officePart .= '('.Auth::user()->office->office_type.')';
        }

        return $this->documentNumberPrefix($typeObj, $officePart).'-'.($lastNumber + 1).'-'.date('Y');
    }

    private function documentNumberPrefix($type, string $officeWithType): string
    {
        $template = $type['number_prefix'] ?: '{office_with_type}-{type}';

        return strtr($template, [
            '{office}' => Auth::user()->office->abbreviation,
            '{office_with_type}' => $officeWithType,
            '{type}' => $type['abbreviation'],
        ]);
    }

    private function processAttachments(Document $document)
    {
        if ($this->original_document_id && DocumentGenerationRule::where('source_document_type_id', Document::find($this->original_document_id)?->document_type_id)->where('target_document_type_id', $this->document_type_id)->where('is_active', true)->exists()) {
            $originalDoc = Document::find($this->original_document_id);
            if ($originalDoc) {
                $document->attachments()->create([
                    'attachment_document_id' => $this->original_document_id,
                    'name' => $originalDoc->document_number,
                    'status' => 'Generated '.$this->document_type,
                    'file_type' => 'pdf',
                    'is_upload' => false,
                ]);
                $originalDoc->update([
                    'status' => 'Generated '.$this->document_type,
                ]);

                $userId = Auth::id();
                if ($originalDoc->steps()->where('step_type', 'action')->exists()) {
                    $actionStep = $originalDoc->steps()
                        ->with('office.actingHead', 'office.head')
                        ->where('step_type', 'action')
                        ->where('status', 'Pending')
                        ->first();

                    if ($actionStep?->isAssignedTo(Auth::user())) {
                        $actionStep->update([
                            'processed_at' => now(),
                            'user_id' => Auth::id(),
                            'status' => 'Approved',
                            'comments' => 'Generated downstream document: '.$document->document_number,
                        ]);
                    }

                    $originalDoc->logs()->create([
                        'user_id' => $userId,
                        'action' => 'Generated Downstream Document',
                        'description' => Auth::user()->office->name.' generated '.$this->document_type.' ('.$document->document_number.') from this document.',
                    ]);
                }
            }
        }

        if (! empty($this->existingAttachments)) {
            foreach ($this->existingAttachments as $existing) {
                if (! $this->selectedType()?->allow_attachments && ($existing['is_upload'] ?? true)) {
                    continue;
                }
                $document->attachments()->create([
                    'name' => $existing['name'],
                    'file_url' => $existing['file_url'],
                    'file_type' => $existing['file_type'],
                    'status' => 'Sent',
                    'is_upload' => $existing['is_upload'] ?? true,
                    'attachment_document_id' => $existing['attachment_document_id'] ?? null,
                ]);
            }
        }

        foreach ($this->selectedType()?->allow_attachments ? $this->attachments : [] as $file) {
            $path = $file->store('attachments', 'public');
            $document->attachments()->create([
                'name' => $file->getClientOriginalName(),
                'status' => 'Sent',
                'file_url' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'is_upload' => true,
            ]);
        }
    }

    public function removeAttachment($filename)
    {
        $this->attachments = collect($this->attachments)
            ->reject(fn ($file) => $file->getClientOriginalName() === $filename)
            ->values()
            ->all();
    }

    private function processSpecialDocumentTypes(Document $document)
    {
        if ($this->external_document_id) {
            ExternalDocument::where('id', $this->external_document_id)->update(['document_id' => $document->id]);
        }
    }

    private function processDocumentSteps(Document $document)
    {
        if (($this->selectedType()?->document_level ?? 'Inter') === 'Intra') {
            return;
        }

        $hasConfiguredFlow = DocumentFlowStage::where('document_type_id', $this->document_type_id)->exists();
        if ($hasConfiguredFlow) {
            $this->processConfiguredFlow($document);
            $this->processCarbonCopies($document);

            return;
        }

        $this->processCarbonCopies($document);
    }

    private function processConfiguredFlow(Document $document): void
    {
        $stages = DocumentFlowStage::with('office.head', 'office.actingHead', 'workflowCondition')
            ->where('document_type_id', $this->document_type_id)
            ->orderBy('sequence')->orderBy('id')->get();
        $sequence = 1;

        // The creator's existing routing checkboxes are populated exclusively by
        // configured routing stages.
        foreach ($stages->where('stage_type', 'routing') as $stage) {
            $manuallySelectedConditionalStage = $stage->is_selectable && $stage->workflow_condition_id
                && (bool) ($this->selectedFlowStages[(string) $stage->id] ?? false);
            if ($this->configuredStageSelected($stage) && ($this->flowConditionMatches($stage) || $manuallySelectedConditionalStage)) {
                $this->createConfiguredStep($document, $stage, $sequence++);
            }
        }

        $configuredSignatories = $stages->where('stage_type', 'signatory');
        $signatories = collect($this->signatories);
        $applicableRequiredSignatories = $configuredSignatories
            ->where('is_required', true)
            ->filter(fn ($stage) => ! $stage->workflow_condition_id
                || ! $stage->workflowCondition?->is_active
                || $this->flowConditionMatches($stage));

        foreach ($applicableRequiredSignatories as $required) {
            if (! $signatories->contains(fn ($item) => (int) ($item['office_id'] ?? 0) === $required->office_id && strcasecmp($item['role'] ?? '', $required->label) === 0)) {
                $signatories->push(['role' => $required->label, 'office_id' => $required->office_id]);
            }
        }

        foreach ($signatories as $signatory) {
            $role = $signatory['role'] ?? '';
            $officeId = (int) ($signatory['office_id'] ?? 0);
            $configured = $configuredSignatories->first(fn ($stage) => $stage->office_id === $officeId && strcasecmp($stage->label, $role) === 0);
            $controlled = $configuredSignatories->contains(fn ($stage) => strcasecmp($stage->label, $role) === 0);

            if ($controlled && ! $configured) {
                throw ValidationException::withMessages(['signatories' => "{$role} may only use an office allowed in Document Flow."]);
            }

            if ($configured) {
                if (! $this->flowConditionMatches($configured)) {
                    continue;
                }

                $this->createConfiguredStep($document, $configured, $sequence++);

                continue;
            }

            // Labels without configured stages remain available for ad-hoc signatories.
            $office = Office::with('head', 'actingHead')->find($officeId);
            $assignee = $office ? $this->documentTypeAssignee($office) : null;
            if (! $assignee) {
                throw ValidationException::withMessages(['signatories' => "{$role} has no eligible office head or OIC."]);
            }
            $document->steps()->create([
                'user_id' => $assignee->id, 'office_id' => $office->id,
                'assigned_user_id' => $assignee->id,
                'step_type' => 'signatory', 'step_label' => $role,
                'signatory_name' => $assignee->name,
                'signatory_position' => ($this->selectedType()?->allow_oic_signature ?? true)
                    ? $office->workflowAssigneePosition() : $assignee->position,
                'sequence' => $sequence++, 'status' => 'Pending',
            ]);
        }

        foreach ($stages->where('stage_type', 'action') as $stage) {
            if ($this->configuredStageSelected($stage) && $this->flowConditionMatches($stage)) {
                $this->createConfiguredStep($document, $stage, $sequence++);
            }
        }

        if ($this->external_document_id) {
            ExternalDocument::where('id', $this->external_document_id)->update(['document_id' => $document->id]);
        }
    }

    private function flowConditionMatches(DocumentFlowStage $stage): bool
    {
        if (! $stage->workflow_condition_id) {
            return true;
        }
        if (! $stage->workflowCondition?->is_active) {
            return true;
        }
        $actual = $this->conditionValues[(string) $stage->workflow_condition_id] ?? null;
        $expected = $stage->condition_value;

        return match ($stage->condition_operator) {
            'not_equals' => (string) $actual !== (string) $expected,
            'greater_than' => is_numeric($actual) && (float) $actual > (float) $expected,
            'less_than' => is_numeric($actual) && (float) $actual < (float) $expected,
            'contains' => str_contains(mb_strtolower((string) $actual), mb_strtolower((string) $expected)),
            default => (string) (int) ($actual === true) === $expected || (string) $actual === (string) $expected,
        };
    }

    private function flowConditionArrayMatches(array $stage): bool
    {
        if (empty($stage['workflow_condition_id']) || ! ($stage['workflow_condition']['is_active'] ?? false)) {
            return false;
        }
        $actual = $this->conditionValues[(string) $stage['workflow_condition_id']] ?? null;
        $expected = $stage['condition_value'] ?? null;

        return match ($stage['condition_operator'] ?? 'equals') {
            'not_equals' => (string) $actual !== (string) $expected,
            'greater_than' => is_numeric($actual) && (float) $actual > (float) $expected,
            'less_than' => is_numeric($actual) && (float) $actual < (float) $expected,
            'contains' => str_contains(mb_strtolower((string) $actual), mb_strtolower((string) $expected)),
            default => (string) (int) ($actual === true) === (string) $expected || (string) $actual === (string) $expected,
        };
    }

    private function createConfiguredStep(Document $document, DocumentFlowStage $stage, int $sequence): void
    {
        $office = $stage->office;
        $assignee = $office ? $this->documentTypeAssignee($office) : null;
        if (! $assignee) {
            throw ValidationException::withMessages(['document_type_id' => "{$stage->label} has no eligible office head or OIC."]);
        }
        $namedOfficial = $stage->stage_type === 'routing'
            ? ($office->head ?? $assignee)
            : $assignee;
        $document->steps()->create([
            'user_id' => $assignee->id, 'office_id' => $office->id,
            'assigned_user_id' => $namedOfficial->id,
            'step_type' => $stage->stage_type, 'step_label' => $stage->label,
            'signatory_name' => $namedOfficial->name,
            'signatory_position' => $stage->stage_type === 'routing'
                ? $namedOfficial->position
                : (($this->selectedType()?->allow_oic_signature ?? true)
                    ? $office->workflowAssigneePosition() : $assignee->position),
            'sequence' => $sequence, 'status' => 'Pending',
        ]);
    }

    private function documentTypeAssignee(Office $office)
    {
        if (! ($this->selectedType()?->allow_oic_signature ?? true)) {
            return $office->head && ! $office->head->trashed() ? $office->head : null;
        }

        return $office->workflow_assignee;
    }

    private function configuredStageSelected(DocumentFlowStage $stage): bool
    {
        if ($stage->is_required && $stage->workflow_condition_id) {
            return $this->flowConditionMatches($stage)
                || ($stage->is_selectable && (bool) ($this->selectedFlowStages[(string) $stage->id] ?? false));
        }
        if ($stage->is_required || ! $stage->is_selectable) {
            return true;
        }

        if ($stage->stage_type === 'routing') {
            return (bool) ($this->selectedFlowStages[(string) $stage->id] ?? false);
        }

        if ($stage->stage_type === 'signatory') {
            return collect($this->signatories)->contains(fn ($signatory) => (int) ($signatory['office_id'] ?? 0) === $stage->office_id);
        }

        return (bool) ($this->selectedFlowStages[(string) $stage->id] ?? false);
    }

    private function processCarbonCopies(Document $document): void
    {
        if (! $this->selectedType()?->show_carbon_copy) {
            return;
        }
        foreach ($this->cf_offices as $cfId) {
            $office = $this->allOffices[$cfId] ?? null;
            if ($office && $office->workflow_assignee) {
                $document->cfs()->create([
                    'user_id' => $office->workflow_assignee->id,
                ]);
            }
        }
    }

    private function handleSessionData()
    {
        $data = session()->pull('redirect_data') ?? session()->pull('document_query');

        if (! $data) {
            return false;
        }

        $this->original_document_id = $data['original_document_id'] ?? $data['id'] ?? null;
        $this->external_document_id = $data['external_document_id'] ?? $data['id'] ?? null;
        $this->redirect_mode = $data['redirect_mode'] ?? null;
        $this->document_to_id = $data['to'] ?? $data['to_id'] ?? null;
        $this->document_to_text = $data['to'] ?? null;
        $this->document_from_id = $data['from'] ?? null;
        $this->document_type_id = $data['document_type_id'] ?? null;
        $this->document_type = $data['document_type'] ?? null;
        $this->subject = $data['subject'] ?? '';
        $this->content = $data['content'] ?? null;
        $this->thru = $data['thru'] ?? null;
        $this->cf_offices = $data['cf'] ?? [];

        $this->handleUpdateDocumentType();

        return true;
    }

    private function handlePassedData($number, $draft_id)
    {
        if ($this->redirect_mode == null) {
            return;
        }

        $document = null;

        if ($this->redirect_mode === 'revision' && $number) {
            $document = Document::where('document_number', $number)->first();
        } elseif ($this->redirect_mode === 'edit' && $draft_id) {
            $document = Document::find($draft_id);
        }

        if (! $document) {
            return;
        }

        if ($this->redirect_mode === 'edit') {
            abort_unless(
                $document->status === 'Draft'
                && ($document->created_by === Auth::id() || in_array($document->from_id, Auth::user()->workflowOfficeIds(), true)),
                403,
                'You do not have permission to edit this draft.'
            );
        }

        $this->original_document_id = $document->id;
        $this->document_from_id = $document->from_id;
        $this->document_to_id = $document->to_id;
        $this->document_type_id = $document->document_type_id;
        $this->thru = $document->thru;
        $this->subject = $document->subject;
        $this->content = $document->content;
        $this->existingAttachments = $document->all_attachments->map(function ($attachment) {
            $data = $attachment->toArray();
            $data['type'] = $attachment->type;
            $data['name'] = $attachment->name ?? $attachment->document_number;

            return $data;
        })->toArray();

        $this->handleUpdateDocumentType();

        foreach ($document->steps()->where('step_type', 'signatory')->get() as $s) {
            $incomingSignatories[] = [
                'role' => $s->step_label,
                'role_type' => in_array($s->step_label, ['Reviewed by', 'Recommending Approval'], true)
                    ? $s->step_label
                    : 'custom',
                'office_id' => $s->user->office->id ?? null,
                'locked' => false,
            ];
        }

        if (! empty($incomingSignatories)) {
            foreach ($incomingSignatories as $newSig) {
                $isDuplicate = collect($this->signatories)->contains(function ($existing) use ($newSig) {
                    return $existing['office_id'] == $newSig['office_id'] && $existing['role'] === $newSig['role'];
                });

                if (! $isDuplicate) {
                    $this->addSignatory($newSig);
                }
            }
        }

        $this->cf_offices = $document->cfs->pluck('user.office.id')->toArray() ?? [];

        $existingRoutingSteps = $document->steps()->where('step_type', 'routing')->get();
        foreach (collect($this->flowStages)->where('stage_type', 'routing') as $stage) {
            $wasPreviouslyRouted = $existingRoutingSteps->contains(fn ($step) => (int) $step->office_id === (int) $stage['office_id']
                && $step->step_label === $stage['label']
            );
            $this->selectedFlowStages[(string) $stage['id']] = $wasPreviouslyRouted;

            if ($wasPreviouslyRouted && ($stage['workflow_condition']['key'] ?? null) === 'has_budget_implications') {
                $this->conditionValues[(string) $stage['workflow_condition_id']] = true;
            }
        }

        if ($document->status === 'Draft') {
            $this->redirect_mode = 'edit';
            $this->revision_document_number = $document->document_number;
        } else {
            abort_unless(
                $document->isRevisableBy(Auth::user()),
                403,
                'Only the original writer may revise a rejected or returned document.'
            );

            $this->redirect_mode = 'revision';
            $root = $document->revisionRoot();
            $this->original_document_id = $root->id;
            $this->original_document_number = $root->document_number;
            $this->revision_document_number = $this->nextRevisionNumber($root);
        }
    }

    private function nextRevisionNumber(Document $root): string
    {
        if (! preg_match('/^(.*-)(\d+)[a-z]*(-\d{4})$/i', $root->document_number, $matches)) {
            throw ValidationException::withMessages(['document_number' => 'This document number cannot be revised automatically.']);
        }

        $count = $root->revisions()->count();

        return $matches[1].$matches[2].chr(ord('a') + $count).$matches[3];
    }

    private function headIsTemporarilyRelieved(): bool
    {
        $office = $this->document_from_id
            ? Office::find($this->document_from_id)
            : Auth::user()?->office;

        return $office !== null
            && $office->head_id === Auth::id()
            && $office->acting_head_id !== null
            && $office->acting_head_id !== Auth::id();
    }

    public function viewAttachment($id, $type)
    {
        if ($type == 'internal') {
            $this->selectedAttachment = DocumentAttachment::find($id);
        } elseif ($type == 'external') {
            $this->selectedAttachment = ExternalDocument::find($id);
        }

        if (! $this->selectedAttachment->is_upload && $type == 'internal') {
            $attachment_document = $this->selectedAttachment->attachmentDocument;
            $attachment_query = $this->processPDF($attachment_document);
            $key = uniqid();
            session([$key => $attachment_query]);
            $this->attachmentPreviewUrl = '/document/preview?'.$key;
        } else {
            $this->attachmentPreviewUrl = asset('storage/'.$this->selectedAttachment->file_url);
        }

        $this->modal('view-attachment-modal')->show();
    }
}
