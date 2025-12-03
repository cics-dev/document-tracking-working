<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\UserController;
use App\Models\Document;
use App\Models\Office;
use App\Models\ExternalDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentForReview;

class CreateDocument extends Component
{
    use WithFileUploads;

    #[Validate(['attachments.*' => 'max:5120'])] // 5MB per file
    public $attachments = [];
    
    // Form Properties
    public $original_document_number;
    public $revision_document_number;
    public $original_document_id = null;
    public $external_document_id = null;
    public $office_type = '';
    public $document_type = ''; // The Abbreviation (e.g., RLM)
    
    // Inputs
    public $document_type_id = '';
    public $document_to_id = '';
    public $document_to_text = '';
    public $document_from_id = '';
    public $thru = '';
    public $subject = '';
    public $content = '';
    public $attachment = ''; // Legacy/Single attachment container
    
    // Arrays & Collections
    public $signatories = [];
    public $users = [];
    public $types = [];
    public $offices = []; // Will be a keyed collection
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

    // --- Lifecycle & Setup ---

    public function mount($number = null)
    {
        $this->office_type = Auth::user()->office->office_type;
        
        // Load Data
        $this->users = app(UserController::class)->index(false);
        $this->types = app(DocumentTypeController::class)->index(Auth::user());
        
        // Optimization: Key offices by ID for O(1) lookup later
        $officesData = app(OfficeController::class)->index(Auth::user()->office->office_type, false);
        $this->offices = collect($officesData)->keyBy('id');

        $this->handleSessionData() ?: $this->handleRevisionData($number);
    }

    public function loadInitialContent()
    {
        $this->readyToLoad = true;
        // Example ID 5: Automatically format subject if specific type
        if ($this->document_type_id == 5) {
            $this->updateContentWithSubject(); 
        }
    }

    public function render()
    {
        return view('livewire.documents.create-document');
    }

    // --- Interaction Methods ---

    public function handleUpdateDocumentType()
    {
        $typeObj = collect($this->types)->firstWhere('id', $this->document_type_id);
        $this->document_type = $typeObj ? $typeObj->abbreviation : 'Intra';
        $this->document_type == ''? $this->document_type='Intra':$this->document_type;

        // 1. Reset Signatories
        $this->signatories = [];

        // 2. RLM Logic: Add Locked University President
        if ($this->document_type === 'RLM') {
            // Default to first office or null
            $this->document_to_id = $this->offices->first()['id'] ?? null;
            $this->document_to_text = null;

            // Add the Locked Signatory (Assuming Office ID 1 is University President)
            $this->signatories[] = [
                'role' => 'Approved by', 
                'office_id' => 1, // <--- ID for University President
                'locked' => true  // <--- Flag to prevent deletion/editing
            ];
        } 
        elseif (in_array($this->document_type, ['ECLR', 'Intra'])) {
            $this->document_to_id = null;
            $this->document_to_text != ''?:'';
        } else {
            $this->document_to_id = null;
            $this->document_to_text = null;
        }
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

    public function addSignatory()
    {
        $newSignatory = ['role' => '', 'office_id' => '', 'locked' => false];

        // Check if the last signatory is locked (The President)
        $lastIndex = count($this->signatories) - 1;

        if ($lastIndex >= 0 && ($this->signatories[$lastIndex]['locked'] ?? false)) {
            // Insert BEFORE the last element so President stays at the bottom
            array_splice($this->signatories, $lastIndex, 0, [$newSignatory]);
        } else {
            // Normal append
            $this->signatories[] = $newSignatory;
        }
    }

    public function removeSignatory($index)
    {
        // Prevent removing locked rows
        if ($this->signatories[$index]['locked'] ?? false) {
            return;
        }

        unset($this->signatories[$index]);
        $this->signatories = array_values($this->signatories);
    }

    public function removeAttachment($filename)
    {
        $this->attachments = collect($this->attachments)
            ->reject(fn($file) => $file->getClientOriginalName() === $filename)
            ->values()
            ->all();
    }

    // --- Content Updates (Quill) ---

    public function updateContentWithTo()
    {
        if ($this->document_type === 'ECLR') $this->formatECLRContent();
    }

    public function updateContentWithSubject()
    {
        if ($this->document_type === 'ECLR') $this->formatECLRContent();
    }

    private function formatECLRContent()
    {
        $html = '<strong>' . strtoupper($this->document_to_text) . '</strong><p>[insert position here]</p><p>[insert office here]</p><p>[insert office address here]</p><br><br><p>Subject: <b>' . strtoupper($this->subject) . '</b></p><br><br>[Insert your salutation]<br><br>[Start your message here]';
        
        $this->dispatch('update-quill', ['content' => $html]);
    }

    // --- Main Logic: Submit & Preview ---

    public function previewDocument()
    {
        $fromUser = $this->document_from_id ? Office::find($this->document_from_id)->head : Auth::user();
        
        // Prepare "To" Details
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

        // Prepare "From" Details
        $fromName = $fromUser->name . ($fromUser->profile->title ? ', ' . $fromUser->profile->title : '');
        $fromPosition = $fromUser->position ?? 'N/A';
        if ($fromUser->position != 'University President' && $fromPosition != 'N/A') {
            $fromPosition .= ', ' . $fromUser->office->name;
        }

        // Prepare Doc Number & Type
        $typeObj = collect($this->types)->firstWhere('id', $this->document_type_id);
        $docTypeAbbr = $typeObj['abbreviation'] ?? 'N/A';
        
        $docNumber = ($this->document_type != 'Intra')
            ? Auth::user()->office->abbreviation . '(' . Auth::user()->office->office_type . ')-' . $docTypeAbbr . '-_____-' . date('Y')
            : 'CM-' . Auth::user()->office->abbreviation . '-_____-' . date('Y');

        // Prepare Signatories & CFs
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

        // Store in Session
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
        $status = $isSend ? 'sent' : 'draft';

        // 1. Validation Logic
        if ($isSend) {
            $this->validateForSend();
        } else {
            // Minimal validation for drafts
            $this->validate([
                'document_type_id' => 'required',
                'subject' => 'required|max:255',
            ]);
        }

        // 2. Prepare Data
        $fromUser = $this->document_from_id ? Office::find($this->document_from_id)->head : Auth::user();
        $docNumber = null;
        
        // Generate number only if sending or if it's not a revision
        if ($isSend && !$this->is_manual_document_number && empty($this->document_number)) {
            $docNumber = $this->generateDocumentNumber();
        }
        else {
            $docNumber = $this->manual_document_number;
        }

        // 3. Create or Update Document
        $data = [
            'from_id' => $fromUser->office->id,
            'to_id' => ($this->document_type === 'Intra' || $this->document_type_id == 5) ? null : $this->document_to_id,
            'to_text' => ($this->document_type === 'Intra' || $this->document_type_id == 5) ? $this->document_to_text : null,
            'document_type_id' => $this->document_type_id,
            'document_number' => $this->revision_document_number ?? $docNumber,
            'subject' => $this->subject,
            'thru' => $this->thru,
            'content' => $this->content,
            'created_by' => Auth::id(),
            'status' => $status,
            'date_sent' => now(), // Set date even for drafts to track last edit, or move to 'updated_at' logic
            'document_level' => $this->document_type === 'Intra' ? 'Intra' : 'Inter',
            'is_revision' => !empty($this->revision_document_number),
            'original_document_id' => !empty($this->revision_document_number) ? $this->original_document_id : null,
        ];

        // Check if updating an existing draft
        $existingDraft = Document::where('id', $this->original_document_id)->where('status', 'draft')->first();

        if ($existingDraft) {
            $existingDraft->update($data);
            $document = $existingDraft;
        } else {
            $document = Document::create($data);
        }

        // 4. Post-Creation Logic (Attachments, Signatories, Routing)
        if ($isSend) {
            $this->processAttachments($document);
            $this->processSpecialDocumentTypes($document);
            $this->processSignatoriesAndRouting($document);

            $recipientEmail = null;
            $recipientName = null;

            // SAFE routing query
            $firstRoute = optional($document)
                ->routings()
                ->whereNull('reviewed_at')
                ->whereNull('returned_at')
                ->orderBy('id', 'asc')
                ->first();

            if ($firstRoute && optional($firstRoute->user)->email) {
                $recipientEmail = $firstRoute->user->email;
                $recipientName = $firstRoute->user->name;
            } else {

                // SAFE signatory query
                $firstSignatory = optional($document)
                    ->signatories()
                    ->whereNull('signed_at')
                    ->whereNull('rejected_at')
                    ->orderBy('sequence', 'asc')
                    ->first();

                if ($firstSignatory && optional($firstSignatory->user)->email) {
                    $recipientEmail = $firstSignatory->user->email;
                    $recipientName = $firstSignatory->user->name;
                }
            }

            // Send email safely
            if (!empty($recipientEmail)) {
                Mail::to($recipientEmail)->send(
                    new DocumentForReview($document, $recipientName ?? 'User')
                );
            }
            
            $document->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'sent',
                'description' => 'Document Sent'
            ]);
        }

        session()->flash('message', $isSend ? 'Document successfully sent.' : 'Document saved as draft.');
        return redirect()->route('documents.list-documents', ['mode' => 'sent']);
    }

    // --- Helper Methods ---

    protected function validateForSend()
    {
        $rules = [
            'document_type_id' => 'required',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'manual_document_number' => $this->is_manual_document_number ? 'required|unique:documents,document_number' : 'nullable',
        ];

        // Conditional Rules based on type
        if (!in_array($this->document_type, ['Intra', 'ECLR', 'IL', 'IOM', 'SO'])) {
             // Standard documents need a recipient
             $rules['document_to_id'] = 'required';
        } elseif ($this->document_type === 'ECLR' || $this->document_type === 'Intra') {
             // These use text input for recipient
             $rules['document_to_text'] = 'required';
        }

        if ($this->document_type == 'RLM') {
            $rules['signatories'] = 'required|array|min:1';
            $rules['signatories.*.role'] = 'required';      // Role cannot be empty
            $rules['signatories.*.office_id'] = 'required'; // Signatory cannot be empty
        }

        // Execute Standard Validation first
        $this->validate($rules, [
            'signatories.required' => 'At least one signatory is required.',
            'signatories.min' => 'At least one signatory is required.',
            'signatories.*.role.required' => 'Role is required.',
            'signatories.*.office_id.required' => 'Signatory is required.',
        ]);
        
        // 2. Custom Check: Must contain "Approved by"
        if ($this->document_type == 'RLM') {
            $hasApprovedBy = collect($this->signatories)->contains('role', 'Approved by');

            if (!$hasApprovedBy) {
                // Add error to the main 'signatories' bag so it shows at the top of the section
                $this->addError('signatories', 'You must include a signatory with the role "Approved by".');
                
                // Throw exception to stop the submit process immediately
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'signatories' => 'You must include a signatory with the role "Approved by".'
                ]);
            }
        }
    }

    private function generateDocumentNumber()
    {
        $typeObj = collect($this->types)->firstWhere('id', $this->document_type_id);
        
        // Find latest number
        $query = Document::where('document_type_id', $this->document_type_id)
            ->where('status', '!=', 'draft')
            ->whereYear('created_at', date('Y'))
            ->orderByDesc('document_number');
            
        // Use Office specific query if not global type? (Original code logic was ambiguous here, cleaned up)
        $latestDoc = $query->first();

        $lastNumber = 0;
        if ($latestDoc) {
            $parts = explode('-', $latestDoc->document_number);
            // Handle variations in format (ZPPSU prefix vs standard)
            $index = isset($parts[4]) ? 3 : 2;
            if (isset($parts[$index]) && is_numeric($parts[$index])) {
                $lastNumber = (int) $parts[$index];
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

        // Prefix ID 5 (Assuming ZPPSU specific)
        if ($this->document_type_id == 5) {
            $num = 'ZPPSU-' . $num;
        }

        return $num;
    }

    private function processAttachments(Document $document)
    {
        // Handle inherited attachments (IOM/SO revisions)
        if (in_array($this->document_type, ['IOM', 'SO']) && $this->original_document_id) {
            $originalDoc = Document::find($this->original_document_id);
            if ($originalDoc) {
                $document->attachments()->create([
                    'attachment_document_id' => $this->original_document_id,
                    'name' => $originalDoc->document_number,
                    'status' => 'approved',
                    'file_type' => 'pdf',
                    'is_upload' => false
                ]);
                $originalDoc->update(['status' => 'Generated ' . $this->document_type]);
            }
        } else {
            // Handle standard uploads
            foreach ($this->attachments as $file) {
                $path = $file->store('attachments', 'public');
                $document->attachments()->create([
                    'name' => $file->getClientOriginalName(),
                    'status' => 'sent',
                    'file_url' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'is_upload' => true
                ]);
            }
        }
    }

    private function processSpecialDocumentTypes(Document $document)
    {
        // IOM Specifics
        if ($this->document_type === 'IOM' && $this->original_document_id) {
            $document->signatories()->create([
                'signatory_label' => 'Approved by',
                'user_id' => 2, // HARDCODED ID: Consider moving to config/const
                'sequence' => 1,
            ]);
        }
        
        // ECLR External Update
        if ($this->document_type === 'ECLR' && $this->external_document_id) {
            ExternalDocument::where('id', $this->external_document_id)->update(['document_id' => $document->id]);
        }
    }

    private function processSignatoriesAndRouting(Document $document)
    {
        if ($this->document_type === 'Intra') return;

        // RLM Routing
        if ($this->document_type === 'RLM') {
            $routeIds = [
                'budget_office' => 19, // HARDCODED
                'motor_pool' => 20,
                'legal_review' => 21,
                'igp_review' => 22,
            ];

            foreach ($this->routingRequirements as $key => $isActive) {
                if ($isActive && isset($routeIds[$key])) {
                    $officeId = $routeIds[$key];
                    // Optimization: Look up user ID from loaded offices if possible, else query
                    $userId = $this->offices[$officeId]['head']['id'] ?? Office::find($officeId)?->head->id;
                    if ($userId) {
                        $document->routings()->create(['user_id' => $userId]);
                    }
                }
            }
            
            if ($this->external_document_id) {
                ExternalDocument::where('id', $this->external_document_id)->update(['document_id' => $document->id]);
            }
        }

        // SO Special Routing
        if ($this->document_type === 'SO') {
            // HARDCODED IDs
            $document->routings()->createMany([
                ['user_id' => 15],
                ['user_id' => 5],
            ]);
        }

        // Auto-add Signatory for certain types
        if (in_array($this->document_type, ['ECLR', 'SO', 'IL'])) {
            $this->signatories[] = ['role' => 'Approved by', 'office_id' => 1]; // HARDCODED Office ID 1
        }

        // Save Signatories
        foreach ($this->signatories as $index => $signatory) {
            $office = $this->offices[$signatory['office_id']] ?? null;
            if ($office && isset($office['head']['id'])) {
                $document->signatories()->create([
                    'signatory_label' => $signatory['role'],
                    'user_id' => $office['head']['id'],
                    'sequence' => $index + 1,
                ]);
            }
        }

        // Save CFs
        foreach ($this->cf_offices as $cfId) {
            $office = $this->offices[$cfId] ?? null;
            if ($office && isset($office['head']['id'])) {
                $document->cfs()->create([
                    'user_id' => $office['head']['id']
                ]);
            }
        }
    }

    // --- Data Loading Helpers ---

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

    private function handleRevisionData($number)
    {
        if (!$number) return;

        $this->redirect_mode = 'revision';
        $this->original_document_number = $number;
        
        $document = Document::where('document_number', $number)->first();
        if (!$document) return;

        $this->original_document_id = $document->id;
        $parts = explode('-', $document->document_number);

        if (count($parts) >= 4) {
            $baseNumber = $parts[2];
            $prefix = $parts[0] . '-' . $parts[1];
            $suffix = $parts[3];

            $original = $document->originalRevisedDocument ?? $document;
            $lastRevision = $original->revisions->last();

            if ($lastRevision) {
                $lastBase = explode('-', $lastRevision->document_number)[2];
                $lastLetter = substr($lastBase, -1);
                $nextLetter = chr(ord($lastLetter) + 1);
                $this->revision_document_number = "{$prefix}-" . intval($baseNumber) . "{$nextLetter}-{$suffix}";
            } else {
                $this->revision_document_number = "{$prefix}-" . intval($baseNumber) . "a-{$suffix}";
            }
        }

        $this->document_to_id = $document->to_id;
        $this->document_type_id = $document->document_type_id;
        $this->subject = $document->subject;
        $this->content = $document->content;
        $this->thru = $document->thru;


        $this->handleUpdateDocumentType();
    }
}