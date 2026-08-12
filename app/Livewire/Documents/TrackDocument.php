<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentController;
use App\Services\DocumentQueryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TrackDocument extends Component
{
    public $document;

    public function mount($number)
    {
        abort_unless(app(DocumentQueryService::class)->canView(Auth::user(), $number), 403, 'You do not have permission to track this document.');
        $response = app(DocumentController::class)->getDocument($number);
        $this->document = $response;
    }

    public function render()
    {
        return view('livewire.documents.track-document')->layout('layouts.app');
    }
}
