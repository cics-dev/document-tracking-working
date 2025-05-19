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
    public string $mode = 'received';

    public function fetchDocuments()
    {
        $response = app(DocumentController::class)->index($this->mode);
        $this->documents = $response;

        foreach ($this->documents as $document) {
            $mySignatory = $document->signatories->firstWhere('user_id', Auth::id());
        
            if ($mySignatory?->signed_at) {
                $document->status = 'Signed';
            } elseif ($mySignatory?->rejected_at) {
                $document->status = 'Rejected';
            } else {
                $document->status = 'Waiting for Signatures';
            }

            // if ($mySignatory?->viewed_at) $document->viewed_at = $mySignatory?->viewed_at;
        }     
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
