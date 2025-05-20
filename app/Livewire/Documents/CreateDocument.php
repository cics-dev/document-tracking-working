<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\UserController;
use App\Models\Document;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateDocument extends Component
{
    public $office_type = '';
    public $document_type = '';
    public $subject = '';
    public $content = '';
    public $document_type_id = '';
    public $document_to_id = '';
    public $signatories = [];
    public $users = [];
    public $types;
    public $offices;
    public $cf_offices = [];
    public $selected_cf_office = '';

    public function handleUpdateDocumentType()
    {
        $this->document_type = $this->types->firstWhere('id',$this->document_type_id)->abbreviation;
    }

    public function fetchUsers()
    {
        $response = app(UserController::class)->index();
        $this->users = $response;
    }

    public function fetchOffices()
    {
        $response = app(OfficeController::class)->index(Auth::user()->office->office_type);
        $this->offices = $response;
    }

    public function fetchDocumentTypes()
    {
        $response = app(DocumentTypeController::class)->index(Auth::user()->office->office_type);
        $this->types = $response;

        if ($this->document_type_id != '') $this->document_type = $this->types->firstWhere('id',$this->document_type_id)->abbreviation;
    }

    public function mount()
    {
        // Pre-fill properties with session data
        $data = session('redirect_data');

        if ($data) {
            $this->document_to_id = $data['to'];
            $this->document_type_id = $data['document_type_id'];
            $this->subject = $data['subject'];
            $this->content = $data['content'];
            $this->cf_offices = $data['cf'];
        }
        
        $this->signatories = [];
        $this->office_type = Auth::user()->office->office_type;
        $this->users = [];
        $this->fetchUsers();
        $this->offices = [];
        $this->fetchOffices();
        $this->types = [];
        $this->fetchDocumentTypes();
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
        $this->cf_offices = array_filter($this->cf_offices, fn($id) => $id != $officeId);
    }

    public function addSignatory()
    {
        $this->signatories[] = ['role' => '', 'office_id' => ''];
    }

    public function removeSignatory($index)
    {
        unset($this->signatories[$index]);
        $this->signatories = array_values($this->signatories);
    }

    public function render()
    {
        return view('livewire.documents.create-document');
    }

    public function submitDocument($action)
    {
        if ($action === 'preview') {
            $toOffice = collect($this->offices)->firstWhere('id', $this->document_to_id);
            $toName = $toOffice['head']['name'] ?? 'N/A';
            $toPosition = $toOffice['head']['position'] ?? 'N/A';
            if ($toPosition !== 'University President' && $toPosition != 'N/A') {
                $toPosition .= ', ' . $toOffice['name'];
            }

            $fromName = Auth::user()->name . (Auth::user()->profile->title != '' ? ', ' . Auth::user()->profile->title : '');
            $fromPosition = Auth::user()->position ?? 'N/A';
            if (Auth::user()->position !== 'University President' && $fromPosition != 'N/A') {
                $fromPosition .= ', ' . Auth::user()->office->name;
            }

            $type = collect($this->types)->firstWhere('id', $this->document_type_id);
            $documentType = $type['name'] ?? 'N/A';
            $documentTypeAbbr = $type['abbreviation'] ?? 'N/A';
            $documentNumber = Auth::user()->office->abbreviation . '(' . Auth::user()->office->office_type . ')' . '-' . $documentTypeAbbr . '-_____-' . date('Y');

            $signatories = collect($this->signatories)->map(function ($signatory) {
                return [
                    'role' => $signatory['role'],
                    'user_name' => collect($this->offices)->firstWhere('id', $signatory['office_id'])->head['name'] ?? '',
                    'position' => collect($this->offices)->firstWhere('id', $signatory['office_id'])->head['position'] ?? '',
                ];
            });

            $cfs = collect($this->cf_offices)->map(function ($cfId) {
                $office = collect($this->offices)->firstWhere('id', $cfId);
            
                return [
                    'name' => $office['name'] ?? 'Unnamed',
                ];
            });

            $query = http_build_query([
                'action' => $action,
                'subject' => $this->subject,
                'content' => $this->content,
                'toName' => $toName,
                'toPosition' => $toPosition,
                'fromName' => $fromName,
                'fromPosition' => $fromPosition,
                'documentType' => $documentType,
                'documentNumber' => $documentNumber,
                'signatories' => $signatories->toJson(),
                'cfs' => $cfs->toJson()
            ]);

            $this->dispatch('open-preview-tab', [
                'url' => '/document/preview?' . $query
            ]);
        }
        else {
            $status = $action === 'draft' ? 'draft' : 'sent';
            $office = Auth::user()->office;
            $documentType = collect($this->types)->firstWhere('id', $this->document_type_id);
            $docNumber = null;
            
            if ($status != 'draft') {
                $latestDoc = $office->sentDocuments()
                    ->where('document_type_id', $this->document_type_id)
                    ->where('status', '!=', 'draft')
                    ->whereYear('created_at', date('Y'))
                    ->latest('created_at')
                    ->first();

                $lastNumber = 0;

                if ($latestDoc) {
                    $parts = explode('-', $latestDoc->document_number);
                    if (isset($parts[2])) {
                        $lastNumber = (int) $parts[2];
                    }
                }

                $docNumber = Auth::user()->office->abbreviation . 
                    (Auth::user()->office->office_type != ''?('(' . Auth::user()->office->office_type . ')'):'')
                    . '-' . $documentType['abbreviation'] . '-' .($lastNumber + 1). '-' . date('Y');
            }
                
            $document = Document::create([
                'from_id' => Auth::user()->office->id,
                'to_id' => $this->document_to_id,
                'document_type_id' => $this->document_type_id,
                'document_number' => $docNumber,
                'subject' => $this->subject,
                'content' => $this->content,
                'created_by' => Auth::id(),
                'status' => $status,
                'date_sent' => now(),
            ]);

            $document->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'Document Sent'
            ]);

            if(!empty($this->signatories)) {
                foreach ($this->signatories as $index => $signatory) {
                    $document->signatories()->create([
                        'signatory_label' => $signatory['role'],
                        'user_id' => collect($this->offices)->firstWhere('id', $signatory['office_id'])['head']['id'] ?? null,
                        'sequence' => $index + 1,
                    ]);
                }
            }

            if (!empty($this->cf_offices)) {
                foreach ($this->cf_offices as $index => $cf) {
                    $document->cfs()->create([
                        'user_id'=>Office::find($cf)->head->id
                    //     'signatory_label' => $cf['role'],
                    //     'user_id' => collect($this->offices)->firstWhere('id', $signatory['office_id'])['head']['id'] ?? null,
                    //     'sequence' => $index + 1,
                    ]);
                }
            }

            session()->flash('message', $status === 'draft' ? 'Document saved as draft.' : 'Document successfully sent.');

            return redirect()->route('documents.list-documents', ['mode' => 'sent']);
        }
    }
}