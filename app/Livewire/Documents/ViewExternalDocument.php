<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use App\Models\ExternalDocument;

class ViewExternalDocument extends Component
{
    public $previewUrl;

    public function mount($id)
    {
        $document = ExternalDocument::find($id);
        $this->previewUrl = asset('storage/' . $document->file_url);
    }

    public function render()
    {
        return view('livewire.documents.view-external-document');
    }
}
