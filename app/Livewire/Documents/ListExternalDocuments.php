<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use App\Models\ExternalDocument;
use Illuminate\Support\Facades\Auth;

class ListExternalDocuments extends Component
{
    public $documents;

    public function mount()
    {
        if (Auth::user()->position == 'Staff' || Auth::user()->position == 'University President' || Auth::user()->office->name == 'Records Section') {
            $this->documents = ExternalDocument::all();
        }
        else {
            $this->documents = ExternalDocument::where('to_id', Auth::user()->office_id)->get();
        }
    }

    public function viewDocument($id)
    {
        return redirect()->route('documents.view-external-document', ['id' => $id]);
    }

    public function render()
    {
        return view('livewire.documents.list-external-documents');
    }
}
