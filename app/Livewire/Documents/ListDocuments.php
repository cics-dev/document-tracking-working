<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentController;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ListDocuments extends Component
{
    public $documents = [];
    public string $mode = 'received';

    public function fetchDocuments()
    {
        $response = app(DocumentController::class)->index($this->mode);
        $this->documents = $response;
    }

    public function mount($mode = 'received')
    {
        if (Auth::user()->position === 'Administrator') {
            abort(403, 'Access denied.');
        }

        $this->mode = $mode;
        $this->documents = [];
        $this->fetchDocuments();
    }
    
    public function render()
    {
        return view('livewire.documents.list-documents');
    }
}
