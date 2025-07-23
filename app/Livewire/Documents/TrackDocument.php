<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentController;
use Livewire\Component;

class TrackDocument extends Component
{
    public $document;

    public function mount($number)
    {
        $response = app(DocumentController::class)->getDocument($number);
        $this->document = $response;
    }

    public function render()
    {
        return view('livewire.documents.track-document');
    }
}
