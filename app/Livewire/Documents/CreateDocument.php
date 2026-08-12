<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\UserController;
use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Models\ExternalDocument;
use App\Models\Office;
use App\Models\DocumentFlowStage;
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

    public $routingRequirements = [
        'budget_office' => false,
        'motor_pool' => false,
        'legal_review' => false,
        'igp_review' => false,
    ];

    public array $flowStages = [];

    public array $selectedFlowStages = [];

    public bool $hasBudgetImplications = false;
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
            if ($generatedType) $this->types->push($generatedType);
        }
        $officesData = app(OfficeController::class)->index(Auth::user()->office->office_type, false);
        $this->offices = collect($officesData)->keyBy('id');
        $this->allOffices = Office::with('head', 'actingHead')->orderBy('name')->get()->keyBy('id');

        $this->handleSessionData() ?: $this->handlePassedData($number, $draft_id);
    }

    public function loadInitialContent()
    {
        $this->readyToLoad = true;
        if ($this->document_type === 'ECLR') {
            $this->updateContentWithSubject();
        }
    }

    public function render()
    {
        return view('livewire.documents.create-document')->layout('layouts.app');
    }

    public function handleUpdateDocumentType()
    {
        $typeObj = collect($this->types)->firstWhere('id', $this->document_type_id);
        $this->document_type = $typeObj ? $typeObj->abbreviation : 'Intra';
        $this->document_type = $this->document_type == '' ? 'Intra' : $this->document_type;

        $this->signatories = [];
        $this->flowStages = DocumentFlowStage::with('office', 'workflowCondition')
            ->where('document_type_id', $this->document_type_id)
            ->orderBy('sequence')->orderBy('id')->get()->toArray();
        $this->hasBudgetImplications = false;
        $this->conditionValues = collect($this->flowStages)->pluck('workflow_condition')->filter(fn ($condition) => $condition['is_active'] ?? false)->unique('id')->mapWithKeys(fn ($condition) => [(string) $condition['id'] => $condition['input_type'] === 'boolean' ? false : ''])->all();
        $this->selectedFlowStages = collect($this->flowStages)
            ->filter(fn ($stage) => $stage['is_required'] && (empty($stage['workflow_condition_id']) || $this->flowConditionArrayMatches($stage)))
            ->mapWithKeys(fn ($stage) => [(string) $stage['id'] => true])->all();

        if ($this->document_type === 'RLM') {
            $presidentOffice = $this->presidentOffice();
            $this->document_to_id = $presidentOffice?->id;
            $this->document_to_text = null;

            $requiredConfiguredSignatories = collect($this->flowStages)
                ->where('stage_type', 'signatory')->where('is_required', true);
            if ($requiredConfiguredSignatories->isNotEmpty()) {
                $this->signatories = $requiredConfiguredSignatories->map(fn ($stage) => [
                    'role' => $stage['label'], 'office_id' => $stage['office_id'], 'locked' => true,
                ])->values()->all();
            } elseif ($presidentOffice?->workflow_assignee) {
                $this->signatories[] = [
                    'role' => 'Approved by',
                    'office_id' => $presidentOffice->id,
                    'locked' => true,
                ];
            }
        } elseif (in_array($this->document_type, ['ECLR', 'Intra'])) {
            $this->document_to_id = null;
        } elseif (! in_array($this->document_type, ['IOM', 'SO'], true)) {
            $this->document_to_id = null;
            $this->document_to_text = null;
        }
    }

    public function updatedConditionValues(): void
    {
        foreach ($this->flowStages as $stage) {
            if (($stage['is_required'] ?? false) && ! empty($stage['workflow_condition_id'])) {
                $this->selectedFlowStages[(string) $stage['id']] = $this->flowConditionArrayMatches($stage);
            }
        }
    }

    public function conditionLocksStage(array $stage): bool
    {
        return (bool) ($stage['is_required'] ?? false)
            && ! empty($stage['workflow_condition_id'])
            && $this->flowConditionArrayMatches($stage);
    }

    private function presidentOffice(): ?Office
    {
        return Office::with('head', 'actingHead')->where('workflow_key', 'university_president')->first();
    }

    private function requiredReviewOfficeIds(): array
    {
        return [
            'budget_office' => Office::where('workflow_key', 'budget')->value('id'),
            'motor_pool' => Office::where('workflow_key', 'motor_pool')->value('id'),
            'legal_review' => Office::where('workflow_key', 'legal')->value('id'),
            'igp_review' => Office::where('workflow_key', 'igp')->value('id'),
        ];
    }

    public function addCfOffice()
    {
        if ($this->selected_cf_office && ! in_array($this->selected_cf_office, $this->cf_offices)) {
            $this->cf_offices[] = $this->selected_cf_office;
            $this->selected_cf_office = null;
        }
    }

    public function removeCfOffice($officeId)
    {
        $this->cf_offices = array_diff($this->cf_offices, [$officeId]);
    }

    public function addSignatory($data = null)
    {
        $newSignatory = $data ?? ['role' => '', 'office_id' => '', 'locked' => false];
        $lastIndex = count($this->signatories) - 1;

        if ($lastIndex >= 0 && ($this->signatories[$lastIndex]['locked'] ?? false)) {
            array_splice($this->signatories, $lastIndex, 0, [$newSignatory]);
        } else {
            $this->signatories[] = $newSignatory;
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
        if ($this->document_type === 'ECLR') {
            $this->formatECLRContent();
        }
    }

    public function updateContentWithSubject()
    {
        if ($this->document_type === 'ECLR') {
            $this->formatECLRContent();
        }
    }

    private function formatECLRContent()
    {
        $html = '<strong>'.strtoupper($this->document_to_text).'</strong><p>[insert position here]</p><p>[insert office here]</p><p>[insert office address here]</p><br><br><p>Subject: <b>'.strtoupper($this->subject).'</b></p><br><br>[Insert your salutation]<br><br>[Start your message here]';
        $this->dispatch('update-quill', ['content' => $html]);
    }

    public function previewDocument()
    {
        $fromUser = $this->document_from_id ? Office::find($this->document_from_id)->head : Auth::user();
        $toName = 'N/A';
        $toPosition = 'N/A';

        if ($this->document_type != 'Intra' && $this->document_to_id) {
            $toOffice = $this->offices[$this->document_to_id] ?? null;
            if ($toOffice) {
                $toName = $toOffice['head']['name'] ?? 'N/A';
                $pos = $toOffice['head']['position'] ?? 'N/A';
                $toPosition = ($pos != 'University President' && $pos != 'N/A') ? "$pos, {$toOffice['name']}" : $pos;
            }
        } else {
            $toName = $this->document_to_text;
        }

        $fromName = $fromUser->name.($fromUser->profile->title ? ', '.$fromUser->profile->title : '');
        $fromPosition = $fromUser->position ?? 'N/A';
        if ($fromUser->position != 'University President' && $fromPosition != 'N/A') {
            $fromPosition .= ', '.$fromUser->office->name;
        }

        $typeObj = collect($this->types)->firstWhere('id', $this->document_type_id);
        $docTypeAbbr = $typeObj['abbreviation'] ?? 'N/A';
        $officePart = Auth::user()->office->abbreviation;

        if (Auth::user()->office->office_type) {
            $officePart .= '('.Auth::user()->office->office_type.')';
        }

        $docNumber = ($this->document_type != 'Intra')
            ? $officePart.'-'.$docTypeAbbr.'-_____-'.date('Y')
            : 'CM-'.Auth::user()->office->abbreviation.'-_____-'.date('Y');

        $signatoriesData = collect($this->signatories)->map(function ($sig) {
            $office = $this->offices[$sig['office_id']] ?? null;

            return [
                'role' => $sig['role'],
                'user_name' => $office['head']['name'] ?? '',
                'position' => $office['head']['position'] ?? '',
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
            'office_logo' => $fromUser->office->office_logo,
            'fromPosition' => $fromPosition,
            'documentType' => $this->document_type === 'Intra' ? 'Intra' : ($typeObj['name'] ?? 'N/A'),
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

        $this->ensureDocumentTypeAllowed();
        if ($isSend) {
            $this->validateForSend();
        } else {
            $this->validate([
                'document_type_id' => 'required',
                'subject' => 'required|max:255',
            ]);
        }

        $fromUser = $this->document_from_id ? Office::find($this->document_from_id)->head : Auth::user();
        $docNumber = null;

        if ($isSend && ! $this->is_manual_document_number && empty($this->document_number)) {
            $docNumber = $this->generateDocumentNumber($fromUser->office->id);
        } else {
            $docNumber = $this->manual_document_number;
        }

        $this->ensureGenerationIsAllowed();

        $isRevision = ! empty($this->revision_document_number);
        $isGenerated = in_array($this->document_type, ['IOM', 'SO'], true) && ! empty($this->original_document_id) && ! $isRevision;

        $data = [
            'from_id' => $fromUser->office->id,
            'to_id' => in_array($this->document_type, ['Intra', 'ECLR'], true) ? null : $this->document_to_id,
            'to_text' => in_array($this->document_type, ['Intra', 'ECLR'], true) ? $this->document_to_text : null,
            'document_type_id' => $this->document_type_id,
            'document_number' => $this->revision_document_number ?? $docNumber,
            'subject' => $this->subject,
            'thru' => $this->thru,
            'content' => $this->content,
            'created_by' => Auth::id(),
            'status' => $status,
            'date_sent' => now(),
            'document_level' => $this->document_type === 'Intra' ? 'Intra' : 'Inter',
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
        if (! in_array($this->document_type, ['IOM', 'SO'], true)) {
            return;
        }

        $source = $this->original_document_id ? Document::find($this->original_document_id) : null;
        if (! $source || $source->status !== 'Approved') {
            throw ValidationException::withMessages([
                'document_type_id' => 'An IOM or SO may only be created from an approved source document.',
            ]);
        }

        $sourceType = $source->documentType?->abbreviation;
        $allowed = $this->document_type === 'IOM'
            ? in_array($sourceType, ['RLM', 'IL'], true)
            : $sourceType === 'IL';

        if (! $allowed) {
            throw ValidationException::withMessages([
                'document_type_id' => 'This document type is not a permitted outcome of the selected source document.',
            ]);
        }

        $generationStep = $source->steps()
            ->with('office.actingHead', 'office.head')
            ->where('step_type', 'action')
            ->where('status', 'Pending')
            ->first();
        $canGenerate = $generationStep?->isAssignedTo(Auth::user());

        if (! $canGenerate) {
            throw ValidationException::withMessages([
                'document_type_id' => 'Only the SAO for Administration/Finance may prepare an IOM or SO.',
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
        ];

        if (! in_array($this->document_type, ['Intra', 'ECLR', 'IL', 'IOM', 'SO'])) {
            $rules['document_to_id'] = 'required';
        } elseif ($this->document_type === 'ECLR' || $this->document_type === 'Intra') {
            $rules['document_to_text'] = 'required';
        }

        if ($this->document_type == 'RLM') {
            $rules['signatories'] = 'required|array|min:1';
            $rules['signatories.*.role'] = 'required';
            $rules['signatories.*.office_id'] = 'required';
        }

        $this->validate($rules, [
            'signatories.required' => 'At least one signatory is required.',
            'signatories.min' => 'At least one signatory is required.',
            'signatories.*.role.required' => 'Role is required.',
            'signatories.*.office_id.required' => 'Signatory is required.',
        ]);

        if ($this->document_type == 'RLM') {
            $hasApprovedBy = collect($this->signatories)->contains('role', 'Approved by');

            if (! $hasApprovedBy) {
                $this->addError('signatories', 'You must include a signatory with the role "Approved by".');
                throw ValidationException::withMessages([
                    'signatories' => 'You must include a signatory with the role "Approved by".',
                ]);
            }
        }
    }

    private function ensureDocumentTypeAllowed(): void
    {
        $isRlm = $this->document_type === 'RLM';
        $allowed = DB::table('role_document_types')
            ->where('role_id', Auth::user()->effectiveRoleId())
            ->where('document_type_id', $this->document_type_id)
            ->where('is_allowed', true)
            ->exists();

        if (! $isRlm && ! $allowed) {
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

        if ($this->document_type != 'Intra') {
            $num = $officePart.'-'.$typeObj['abbreviation'].'-'.($lastNumber + 1).'-'.date('Y');
        } else {
            $num = 'CM-'.$officePart.'-'.($lastNumber + 1).'-'.date('Y');
        }

        if ($this->document_type === 'ECLR') {
            $num = 'ZPPSU-'.$num;
        }

        return $num;
    }

    private function processAttachments(Document $document)
    {
        if (in_array($this->document_type, ['IOM', 'SO']) && $this->original_document_id) {
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
                if (in_array($originalDoc->documentType?->abbreviation, ['RLM', 'IL'], true)) {
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

        foreach ($this->attachments as $file) {
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
        if ($this->document_type === 'ECLR' && $this->external_document_id) {
            ExternalDocument::where('id', $this->external_document_id)->update(['document_id' => $document->id]);
        }
    }

    private function processDocumentSteps(Document $document)
    {
        if ($this->document_type === 'Intra') {
            return;
        }

        $hasConfiguredFlow = DocumentFlowStage::where('document_type_id', $this->document_type_id)->exists();
        if ($hasConfiguredFlow) {
            $this->processConfiguredFlow($document);
            $this->processCarbonCopies($document);
            return;
        }

        // Once Document Flow is installed, IL and ECLR have no implicit President
        // signatory. They receive only stages explicitly configured by an admin.
        if (in_array($this->document_type, ['IL', 'ECLR'], true)) {
            $this->processCarbonCopies($document);
            return;
        }

        $sequence = 1;

        if ($this->document_type === 'RLM') {
            $routeIds = $this->requiredReviewOfficeIds();
            foreach ($this->routingRequirements as $key => $isActive) {
                if ($isActive && ! empty($routeIds[$key])) {
                    $officeId = $routeIds[$key];
                    $userId = $this->offices[$officeId]['workflow_assignee']['id']
                        ?? Office::with('actingHead', 'head')->find($officeId)?->workflow_assignee?->id;
                    if ($userId) {
                        $document->steps()->create([
                            'user_id' => $userId,
                            'office_id' => $officeId,
                            'step_type' => 'routing',
                            'step_label' => ucwords(str_replace('_', ' ', $key)),
                            'sequence' => $sequence++,
                            'status' => 'Pending',
                        ]);
                    }
                }
            }

            if ($this->external_document_id) {
                ExternalDocument::where('id', $this->external_document_id)->update(['document_id' => $document->id]);
            }
        }

        if ($this->document_type === 'SO') {
            $caoOffice = Office::with('actingHead', 'head')->where('workflow_key', 'cao')->first();
            if ($caoOffice?->workflow_assignee) {
                $document->steps()->create([
                    'user_id' => $caoOffice->workflow_assignee->id,
                    'office_id' => $caoOffice->id,
                    'step_type' => 'routing',
                    'step_label' => 'Chief Administrative Officer Review',
                    'sequence' => $sequence++,
                    'status' => 'Pending',
                ]);
            }

            $vpafOffice = Office::with('actingHead', 'head')->where('workflow_key', 'vpaf')->first();
            if ($vpafOffice?->workflow_assignee) {
                $document->steps()->create([
                    'user_id' => $vpafOffice->workflow_assignee->id,
                    'office_id' => $vpafOffice->id,
                    'step_type' => 'routing',
                    'step_label' => 'VPAF Review',
                    'sequence' => $sequence++,
                    'status' => 'Pending',
                ]);
            }
        }

        if (in_array($this->document_type, ['SO', 'IOM'])) {
            $president = $this->presidentOffice();
            if (! $president?->workflow_assignee) {
                throw ValidationException::withMessages(['signatories' => 'A University President must be assigned before this document can be sent.']);
            }

            $alreadyAdded = collect($this->signatories)->contains(fn ($s) => $s['office_id'] === $president->id);
            if (! $alreadyAdded && $this->document_type !== 'RLM') {
                $this->signatories[] = ['role' => 'Approved by', 'office_id' => $president->id];
            }
        }

        foreach ($this->signatories as $signatory) {
            $office = $this->offices[$signatory['office_id']] ?? null;
            if ($office && $office->workflow_assignee) {
                $document->steps()->create([
                    'user_id' => $office->workflow_assignee->id,
                    'office_id' => $office['id'],
                    'step_type' => 'signatory',
                    'step_label' => $signatory['role'],
                    'signatory_name' => $office->head?->name ?? $office->workflow_assignee->name,
                    'signatory_position' => $office->head?->position ?? $office->workflow_assignee->position,
                    'sequence' => $sequence++,
                    'status' => 'Pending',
                ]);
            }
        }

        if (in_array($this->document_type, ['RLM', 'IL'])) {
            $hasBudgetRouting = $this->document_type === 'RLM' && ($this->routingRequirements['budget_office'] ?? false);

            $saoOffice = Office::with('actingHead', 'head')
                ->where('workflow_key', $hasBudgetRouting ? 'sao_finance' : 'sao_admin')
                ->first();

            if ($saoOffice?->workflow_assignee) {
                if ($this->document_type === 'RLM') {
                    $actionLabel = 'Generation of IOM';
                } else {
                    // IL can result in either IOM or SO
                    $actionLabel = 'Generation of IOM or SO';
                }

                $document->steps()->create([
                    'user_id' => $saoOffice->workflow_assignee->id,
                    'office_id' => $saoOffice->id,
                    'step_type' => 'action',
                    'step_label' => $actionLabel,
                    'sequence' => $sequence++,
                    'status' => 'Pending',
                ]);
            }
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
        foreach ($configuredSignatories->where('is_required', true) as $required) {
            if (! $signatories->contains(fn ($item) => (int) ($item['office_id'] ?? 0) === $required->office_id && strcasecmp($item['role'] ?? '', $required->label) === 0)) {
                $signatories->push(['role' => $required->label, 'office_id' => $required->office_id]);
            }
        }

        foreach ($signatories as $signatory) {
            $role = $signatory['role'] ?? '';
            $officeId = (int) ($signatory['office_id'] ?? 0);
            $controlled = in_array(strtolower($role), ['recommending approval', 'approved by'], true);
            $configured = $configuredSignatories->first(fn ($stage) => $stage->office_id === $officeId && strcasecmp($stage->label, $role) === 0);

            if ($controlled && ! $configured) {
                throw ValidationException::withMessages(['signatories' => "{$role} may only use an office allowed in Document Flow."]);
            }

            if ($configured) {
                $this->createConfiguredStep($document, $configured, $sequence++);
                continue;
            }

            // Reviewed by, Noted by, Concurred by, and other roles are deliberately
            // unrestricted and do not need a Document Flow entry.
            $office = Office::with('head', 'actingHead')->find($officeId);
            if (! $office?->workflow_assignee) {
                throw ValidationException::withMessages(['signatories' => "{$role} has no assigned office head or OIC."]);
            }
            $document->steps()->create([
                'user_id' => $office->workflow_assignee->id, 'office_id' => $office->id,
                'step_type' => 'signatory', 'step_label' => $role,
                'signatory_name' => $office->head?->name ?? $office->workflow_assignee->name,
                'signatory_position' => $office->head?->position ?? $office->workflow_assignee->position,
                'sequence' => $sequence++, 'status' => 'Pending',
            ]);
        }

        foreach ($stages->where('stage_type', 'action') as $stage) {
            if ($this->configuredStageSelected($stage) && $this->flowConditionMatches($stage)) {
                $this->createConfiguredStep($document, $stage, $sequence++);
            }
        }

        if ($this->document_type === 'RLM' && $this->external_document_id) {
            ExternalDocument::where('id', $this->external_document_id)->update(['document_id' => $document->id]);
        }
    }

    private function flowConditionMatches(DocumentFlowStage $stage): bool
    {
        if (! $stage->workflow_condition_id) {
            return $stage->condition === 'always'
                || ($stage->condition === 'with_budget' && $this->hasBudgetImplications)
                || ($stage->condition === 'without_budget' && ! $this->hasBudgetImplications);
        }
        if (! $stage->workflowCondition?->is_active) return true;
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
        if (empty($stage['workflow_condition_id']) || ! ($stage['workflow_condition']['is_active'] ?? false)) return false;
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
        if (! $office?->workflow_assignee) {
            throw ValidationException::withMessages(['document_type_id' => "{$stage->label} has no assigned office head or OIC."]);
        }
        $document->steps()->create([
            'user_id' => $office->workflow_assignee->id, 'office_id' => $office->id,
            'step_type' => $stage->stage_type, 'step_label' => $stage->label,
            'signatory_name' => $stage->stage_type === 'signatory' ? ($office->head?->name ?? $office->workflow_assignee->name) : null,
            'signatory_position' => $stage->stage_type === 'signatory' ? ($office->head?->position ?? $office->workflow_assignee->position) : null,
            'sequence' => $sequence, 'status' => 'Pending',
        ]);
    }

    private function configuredStageSelected(DocumentFlowStage $stage): bool
    {
        if ($stage->is_required && $stage->workflow_condition_id) {
            return $this->flowConditionMatches($stage)
                || ($stage->is_selectable && (bool) ($this->selectedFlowStages[(string) $stage->id] ?? false));
        }
        if ($stage->is_required || ! $stage->is_selectable) return true;

        if ($stage->stage_type === 'routing') {
            if (array_key_exists((string) $stage->id, $this->selectedFlowStages)) {
                return (bool) $this->selectedFlowStages[(string) $stage->id];
            }
            $key = match ($stage->office?->workflow_key) {
                'budget' => 'budget_office',
                'motor_pool' => 'motor_pool',
                'legal' => 'legal_review',
                'igp' => 'igp_review',
                default => null,
            };
            return $key ? (bool) ($this->routingRequirements[$key] ?? false) : false;
        }

        if ($stage->stage_type === 'signatory') {
            return collect($this->signatories)->contains(fn ($signatory) => (int) ($signatory['office_id'] ?? 0) === $stage->office_id);
        }

        return (bool) ($this->selectedFlowStages[(string) $stage->id] ?? false);
    }

    private function processCarbonCopies(Document $document): void
    {
        foreach ($this->cf_offices as $cfId) {
            $office = $this->offices[$cfId] ?? null;
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

        $this->original_document_id = $document->id;
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

        if ($this->document_type === 'RLM') {
            $routeMap = $this->requiredReviewOfficeIds();
            $existingRouteOfficeIds = $document->steps()->where('step_type', 'routing')->pluck('user.office.id')->toArray();

            foreach ($routeMap as $key => $officeId) {
                $this->routingRequirements[$key] = $officeId && in_array($officeId, $existingRouteOfficeIds);
            }
        }

        if ($document->status === 'Draft') {
            $this->redirect_mode = 'edit';
            $this->revision_document_number = $document->document_number;
        } else {
            if ($document->status !== 'Rejected' || $document->created_by !== Auth::id()) {
                abort(403, 'Only the original creator may revise a rejected document.');
            }

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
