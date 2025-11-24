<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ExternalDocument;
use App\Models\Office;

class ReceiveExternalDocument extends Component
{
    use WithFileUploads;

    #[Validate('array')]
    #[Validate('max:5120')] // Max 5MB total
    public $attachment;
    public $document_from;
    public $document_to_id;
    public $subject;
    public $received_date;

    protected $rules = [
        'document_from' => 'required|string|max:255',
        'document_to_id' => 'required|exists:offices,id',
        'subject' => 'required|string|max:255',
        'attachment' => 'nullable|file|max:102400', // 100MB max
    ];

    public function removeAttachment()
    {
        $this->attachment = null;
    }

    public function fetchUsers()
    {
        $response = app(UserController::class)->index(false);
        $this->users = $response;
    }

    public function fetchOffices()
    {
        $response = app(OfficeController::class)->index(Auth::user()->office->office_type, false);
        $this->offices = $response;
    }

    public function submitDocument()
    {
        $this->validate();

        $path = null;
        $fileType = null;

        if ($this->attachment) {
            $path = $this->attachment->store('attachments', 'public');
            $fileType = $this->attachment->getClientOriginalExtension();
        }

        $document = ExternalDocument::create([
            'from' => $this->document_from,
            'to_id' => $this->document_to_id,
            'subject' => $this->subject,
            'received_date' => now(),
            'file_url' => $path,
            'file_type' => $fileType,
        ]);

        session()->flash('success', 'External Document saved successfully.');

        return redirect()->route('documents.list-external-documents');
    }

    public function render()
    {
        return view('livewire.documents.receive-external-document', [
            'offices' => Office::all(),
        ]);
    }
}