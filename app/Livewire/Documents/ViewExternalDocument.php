<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use App\Models\ExternalDocument;
use App\Models\DocumentType;

class ViewExternalDocument extends Component
{
    public $previewUrl;
    public $document;

    public function mount($id)
    {
        $this->document = ExternalDocument::find($id);
        $this->previewUrl = asset('storage/' . $this->document->file_url);
    }

    public function generateRLM()
    {
        $redirectData = [
            'subject' => 'RE: ' . $this->document->subject,
            'external_document_id' => $this->document->id,
            'document_type_id' => DocumentType::where('abbreviation', 'RLM')->value('id'),
        ];

        session()->flash('redirect_data', $redirectData);
    
        return redirect()->route('documents.create-document');
    }

    public function generateECLR()
    {
        $redirectData = [
            'to' => $this->document->from,
            'subject' => 'RE: ' . $this->document->subject,
            'external_document_id' => $this->document->id,
            'document_type_id' => DocumentType::where('abbreviation', 'ECLR')->value('id'),
        ];

        session()->flash('redirect_data', $redirectData);
    
        return redirect()->route('documents.create-document');
    }

    public function render()
    {
        return view('livewire.documents.view-external-document');
    }
}
