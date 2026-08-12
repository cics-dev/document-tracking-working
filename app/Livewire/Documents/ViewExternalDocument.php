<?php

namespace App\Livewire\Documents;

use App\Models\DocumentType;
use App\Models\ExternalDocument;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ViewExternalDocument extends Component
{
    public $previewUrl;

    public $document;

    public function mount($id)
    {
        $this->document = ExternalDocument::with('toOffice.head', 'toOffice.actingHead')->findOrFail($id);

        $this->document->accessLogs()->firstOrCreate([
            'user_id' => Auth::id(),
            'action' => 'Viewed',
        ]);
        $this->previewUrl = asset('storage/'.$this->document->file_url);
    }

    public function generateRLM()
    {
        $this->assertAssignedRecipient();
        $redirectData = [
            'subject' => 'RE: '.$this->document->subject,
            'external_document_id' => $this->document->id,
            'document_type_id' => DocumentType::where('abbreviation', 'RLM')->value('id'),
            'document_type' => 'RLM',
        ];

        session()->flash('redirect_data', $redirectData);

        return redirect()->route('documents.create-document');
    }

    public function generateECLR()
    {
        $this->assertAssignedRecipient();
        $redirectData = [
            'to' => $this->document->from,
            'subject' => 'RE: '.$this->document->subject,
            'external_document_id' => $this->document->id,
            'document_type_id' => DocumentType::where('abbreviation', 'ECLR')->value('id'),
            'document_type' => 'ECLR',
        ];

        session()->flash('redirect_data', $redirectData);

        return redirect()->route('documents.create-document');
    }

    public function render()
    {
        return view('livewire.documents.view-external-document')->layout('layouts.app');
    }

    private function assertAssignedRecipient(): void
    {
        abort_unless($this->document->toOffice?->workflow_assignee?->is(Auth::user()), 403, 'Only the assigned office may prepare a response or request.');
    }
}
