<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\UserController;
use App\Models\Document;
use App\Models\Office;
use App\Models\DocumentAttachment;
use App\Models\ExternalDocument;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;

class CreateDocument extends Component
{
    use WithFileUploads;

    #[Validate(['attachments.*' => 'file|max:102400'])]
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
    public $cf_offices = [];
    public $selected_cf_office = '';
    
    public $routingRequirements = [
        'budget_office' => false,
        'motor_pool' => false,
        'legal_review' => false,
        'igp_review' => false,
    ];

    public $readyToLoad = false;
    public $redirect_mode = null;

    public $manual_document_number = null; 
    public $is_manual_document_number = false;

    public function mount($number = null, $draft_id = null)
    {
        $this->redirect_mode = $number ? 'revision' : ($draft_id ? 'edit' : null);
        $this->office_type = Auth::user()->office->office_type;
        
        $this->users = app(UserController::class)->index(false);
        $this->types = app(DocumentTypeController::class)->index(Auth::user());
        $officesData = app(OfficeController::class)->index(Auth::user()->office->office_type, false);
        $this->offices = collect($officesData)->keyBy('id');

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

        if ($this->document_type === 'RLM') {
            $presidentOffice = $this->presidentOffice();
            $this->document_to_id = $presidentOffice?->id;
            $this->document_to_text = null;

            if ($presidentOffice?->head_id) {
                $this->signatories[] = [
                    'role' => 'Approved by',
                    'office_id' => $presidentOffice->id,
                    'locked' => true,
                ];
            }
        } 
        elseif (in_array($this->document_type, ['ECLR', 'Intra'])) {
            $this->document_to_id = null;
        } 
        elseif (!in_array($this->document_type, ['IOM', 'SO'], true)) {
            $this->document_to_id = null;
            $this->document_to_text = null;
        }
    }

    private function presidentOffice(): ?Office
    {
        return Office::whereHas('users', fn ($query) => $query->where('position', 'University President'))
            ->with('head')
            ->first();
    }

    private function requiredReviewOfficeIds(): array
    {
        return [
            'budget_office' => Office::where('name', 'Budget Office')->value('id'),
            'motor_pool' => Office::where('name', 'Motorpool Office')->value('id'),
            'legal_review' => Office::where('name', 'Legal Office')->value('id'),
            'igp_review' => Office::where('name', 'Income Generating Program Office')->value('id'),
        ];
    }

    public function addCfOffice()
    {
        if ($this->selected_cf_office && !in_array($this->selected_cf_office, $this->cf_offices)) {
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
        $html = '<strong>' . strtoupper($this->document_to_text) . '</strong><p>[insert position here]</p><p>[insert office here]</p><p>[insert office address here]</p><br><br><p>Subject: <b>' . strtoupper($this->subject) . '</b></p><br><br>[Insert your salutation]<br><br>[Start your message here]';
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

        $fromName = $fromUser->name . ($fromUser->profile->title ? ', ' . $fromUser->profile->title : '');
        $fromPosition = $fromUser->position ?? 'N/A';
        if ($fromUser->position != 'University President' && $fromPosition != 'N/A') {
            $fromPosition .= ', ' . $fromUser->office->name;
        }

        $typeObj = collect($this->types)->firstWhere('id', $this->document_type_id);
        $docTypeAbbr = $typeObj['abbreviation'] ?? 'N/A';
        $officePart = Auth::user()->office->abbreviation;

        if (Auth::user()->office->office_type) {
            $officePart .= '(' . Auth::user()->office->office_type . ')';
        }

        $docNumber = ($this->document_type != 'Intra')
            ? $officePart . '-' . $docTypeAbbr . '-_____-' . date('Y')
            : 'CM-' . Auth::user()->office->abbreviation . '-_____-' . date('Y');

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
            'attachment' => $this->attachment
        ];

        $key = uniqid();
        session([$key => $query]);

        $this->dispatch('open-preview-tab', ['url' => '/document/preview?' . $key]);
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
        
        if ($isSend && !$this->is_manual_document_number && empty($this->document_number)) {
            $docNumber = $this->generateDocumentNumber($fromUser->office->id);
        } else {
            $docNumber = $this->manual_document_number;
        }

        $this->ensureGenerationIsAllowed();

        $isRevision = !empty($this->revision_document_number);
        $isGenerated = in_array($this->document_type, ['IOM', 'SO'], true) && !empty($this->original_document_id) && !$isRevision;

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
            $document = $existingDraft ?: new Document();
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
                'description' => 'Document Sent'
            ]);
        }

        session()->flash('message', $isSend ? 'Document successfully sent.' : 'Document saved as draft.');
        return redirect()->route('documents.list-documents', ['mode' => 'Sent']);
    }

    private function ensureGenerationIsAllowed(): void
    {
        if (!in_array($this->document_type, ['IOM', 'SO'], true)) {
            return;
        }

        $source = $this->original_document_id ? Document::find($this->original_document_id) : null;
        if (!$source || $source->status !== 'Approved') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'document_type_id' => 'An IOM or SO may only be created from an approved source document.',
            ]);
        }

        $sourceType = $source->documentType?->abbreviation;
        $allowed = $this->document_type === 'IOM'
            ? in_array($sourceType, ['RLM', 'IL'], true)
            : $sourceType === 'IL';

        if (!$allowed) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'document_type_id' => 'This document type is not a permitted outcome of the selected source document.',
            ]);
        }

        if (Auth::user()->office?->name !== 'Administration') {
            throw \Illuminate\Validation\ValidationException::withMessages([
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

        if (!in_array($this->document_type, ['Intra', 'ECLR', 'IL', 'IOM', 'SO'])) {
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

            if (!$hasApprovedBy) {
                $this->addError('signatories', 'You must include a signatory with the role "Approved by".');
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'signatories' => 'You must include a signatory with the role "Approved by".'
                ]);
            }
        }
    }

    private function ensureDocumentTypeAllowed(): void
    {
        $isRlm = $this->document_type === 'RLM';
        $allowed = DB::table('role_document_types')
            ->where('role_id', Auth::user()->role_id)
            ->where('document_type_id', $this->document_type_id)
            ->where('is_allowed', true)
            ->exists();

        if (!$isRlm && !$allowed) {
            throw \Illuminate\Validation\ValidationException::withMessages([
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
            if (empty($docNumber)) continue;

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
            $officePart .= '(' . Auth::user()->office->office_type . ')';
        }

        if ($this->document_type != 'Intra') {
            $num = $officePart . '-' . $typeObj['abbreviation'] . '-' . ($lastNumber + 1) . '-' . date('Y');
        } else {
            $num = 'CM-' . $officePart . '-' . ($lastNumber + 1) . '-' . date('Y');
        }

        if ($this->document_type === 'ECLR') {
            $num = 'ZPPSU-' . $num;
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
                    'status' => 'Generated ' . $this->document_type,
                    'file_type' => 'pdf',
                    'is_upload' => false
                ]);
                $originalDoc->update([
                    'status' => 'Generated ' . $this->document_type
                ]);

                // Update original document step and status if generated by SAO-A (10) or SAO-F (16) for RLM (1) or IL (3)
                $userId = Auth::id();
                if (in_array($userId, [10, 16], true) && in_array($originalDoc->document_type_id, [1, 3], true)) {
                    $actionStep = $originalDoc->steps()
                        ->where('user_id', $userId)
                        ->where('step_type', 'action')
                        ->first();

                    if ($actionStep) {
                        $actionStep->update([
                            'processed_at' => now(),
                            'status' => 'Approved',
                            'comments' => 'Generated downstream document: ' . $document->document_number,
                        ]);
                    }

                    // Check if all routing, signatory, and action steps are complete
                    $isComplete = $originalDoc->fresh()->steps()
                        ->whereIn('step_type', ['routing', 'signatory', 'action'])
                        ->where(function ($query) {
                            $query->whereNull('processed_at')
                                  ->orWhereNotIn('status', ['Approved', 'Reviewed']);
                        })
                        ->doesntExist();

                    // if ($isComplete) {
                    //     $originalDoc->update(['status' => 'Approved']);
                    // } else {
                    //     $originalDoc->update(['status' => 'In Process']);
                    // }

                    $originalDoc->logs()->create([
                        'user_id' => $userId,
                        'action' => 'Generated Downstream Document',
                        'description' => Auth::user()->office->name . ' generated ' . $this->document_type . ' (' . $document->document_number . ') from this document.',
                    ]);
                }
            }
        } 

        if (!empty($this->existingAttachments)) {
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
                'is_upload' => true
            ]);
        }
    }

    public function removeAttachment($filename)
    {
        $this->attachments = collect($this->attachments)
            ->reject(fn($file) => $file->getClientOriginalName() === $filename)
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
        if ($this->document_type === 'Intra') return;

        $sequence = 1;

        if ($this->document_type === 'RLM') {
            $routeIds = $this->requiredReviewOfficeIds();
            foreach ($this->routingRequirements as $key => $isActive) {
                if ($isActive && !empty($routeIds[$key])) {
                    $officeId = $routeIds[$key];
                    $userId = $this->offices[$officeId]['head']['id'] ?? Office::find($officeId)?->head->id;
                    if ($userId) {
                        $document->steps()->create([
                            'user_id' => $userId,
                            'step_type' => 'routing',
                            'step_label' => ucwords(str_replace('_', ' ', $key)),
                            'sequence' => $sequence++,
                            'status' => 'Pending'
                        ]);
                    }
                }
            }

            if ($this->external_document_id) {
                ExternalDocument::where('id', $this->external_document_id)->update(['document_id' => $document->id]);
            }
        }

        if (in_array($this->document_type, ['ECLR', 'SO', 'IL', 'IOM'])) {
            $president = $this->presidentOffice();
            if (!$president?->head_id) {
                throw \Illuminate\Validation\ValidationException::withMessages(['signatories' => 'A University President must be assigned before this document can be sent.']);
            }
            
            $alreadyAdded = collect($this->signatories)->contains(fn($s) => $s['office_id'] === $president->id);
            if (!$alreadyAdded && $this->document_type !== 'RLM') {
                $this->signatories[] = ['role' => 'Approved by', 'office_id' => $president->id];
            }
        }

        foreach ($this->signatories as $signatory) {
            $office = $this->offices[$signatory['office_id']] ?? null;
            if ($office && isset($office['head']['id'])) {
                $document->steps()->create([
                    'user_id' => $office['head']['id'],
                    'step_type' => 'signatory',
                    'step_label' => $signatory['role'],
                    'sequence' => $sequence++,
                    'status' => 'Pending'
                ]);
            }
        }

        if (in_array($this->document_type, ['RLM', 'IL'])) {
            $hasBudgetRouting = $this->document_type === 'RLM' && ($this->routingRequirements['budget_office'] ?? false);
            
            $targetSaoRole = $hasBudgetRouting ? 'Supervising Administrative Officer for Finance' : 'Supervising Administrative Officer for Administration';
            
            $saoUser = \App\Models\User::where('position', $targetSaoRole)->first();

            if ($saoUser) {
                if ($this->document_type === 'RLM') {
                    $actionLabel = 'Generation of IOM';
                } else {
                    // IL can result in either IOM or SO
                    $actionLabel = 'Generation of IOM or SO';
                }

                $document->steps()->create([
                    'user_id' => $saoUser->id,
                    'step_type' => 'action',
                    'step_label' => $actionLabel,
                    'sequence' => $sequence++,
                    'status' => 'Pending'
                ]);
            }
        }

        foreach ($this->cf_offices as $cfId) {
            $office = $this->offices[$cfId] ?? null;
            if ($office && isset($office['head']['id'])) {
                $document->cfs()->create([
                    'user_id' => $office['head']['id']
                ]);
            }
        }
    }

    private function handleSessionData()
    {
        $data = session()->pull('redirect_data') ?? session()->pull('document_query');

        if (!$data) return false;

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
        if ($this->redirect_mode == null) return;

        $document = null;

        if ($this->redirect_mode === 'revision' && $number) {
            $document = Document::where('document_number', $number)->first();
        } elseif ($this->redirect_mode === 'edit' && $draft_id) {
            $document = Document::find($draft_id);
        }

        if (!$document) return;

        $this->original_document_id = $document->id;
        $this->document_to_id = $document->to_id;
        $this->document_type_id = $document->document_type_id;
        $this->thru = $document->thru;
        $this->subject = $document->subject;
        $this->content = $document->content;
        $this->existingAttachments = $document->all_attachments->map(function($attachment) {
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

        if (!empty($incomingSignatories)) {
            foreach ($incomingSignatories as $newSig) {
                $isDuplicate = collect($this->signatories)->contains(function ($existing) use ($newSig) {
                    return $existing['office_id'] == $newSig['office_id'] && $existing['role'] === $newSig['role'];
                });

                if (!$isDuplicate) {
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
        if (!preg_match('/^(.*-)(\d+)[a-z]*(-\d{4})$/i', $root->document_number, $matches)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['document_number' => 'This document number cannot be revised automatically.']);
        }

        $count = $root->revisions()->count();
        return $matches[1] . $matches[2] . chr(ord('a') + $count) . $matches[3];
    }

    public function viewAttachment($id, $type)
    {
        if ($type == 'internal') {
            $this->selectedAttachment = DocumentAttachment::find($id);
        } else if ($type == 'external') {
            $this->selectedAttachment = ExternalDocument::find($id);
        }
        
        if (!$this->selectedAttachment->is_upload && $type == 'internal') {
            $attachment_document = $this->selectedAttachment->attachmentDocument;
            $attachment_query = $this->processPDF($attachment_document);
            $key = uniqid();
            session([$key => $attachment_query]);
            $this->attachmentPreviewUrl = '/document/preview?' . $key;
        } else {
            $this->attachmentPreviewUrl = asset('storage/' . $this->selectedAttachment->file_url);
        }

        $this->modal('view-attachment-modal')->show();
    }
}