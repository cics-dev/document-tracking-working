<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentController;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ListDocuments extends Component
{
    public $office_name;
    public $documents = [];
    public $document;
    public $documentTypeTab = 'inter';
    public string $mode = 'received';

    public function fetchDocuments()
    {
        $response = app(DocumentController::class)->index($this->mode);

        $this->documents = $response;

        
        if ($this->documentTypeTab === 'intra') {
            $this->documents = $this->documents->where('document_level', 'Intra');
        } else {
            $this->documents = $this->documents->where('document_level', '!=', 'Intra');
        }  
    }

    public function editDocument($id)
    {
        $document = Document::find($id);
        $document['redirect_mode']='edit';
        session()->flash('document_query', $document);

        return redirect()->route('documents.create-document', ['id' => $id]);
    }

    public function switchDocumentTypeTab($tab)
    {
        $this->documentTypeTab = $tab;
        $this->fetchDocuments();
    }

    public function mount($mode = 'received')
    {
        if (Auth::user()->position === 'Administrator') {
            abort(403, 'Access denied.');
        }
        $this->office_name = Auth::user()->office->name;

        $this->mode = $mode;
        $this->documents = [];
        $this->fetchDocuments();
    }
    
    public function render()
    {
        return view('livewire.documents.list-documents');
    }

    public function viewDocument($number) {
        return redirect()->route('documents.view-document', ['number' => $number]);
    }

    public function trackDocument($number) {
        return redirect()->route('documents.track-document', ['number' => $number]);
    }
}
