<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\UserController;
use Livewire\Component;

class CreateDocument extends Component
{
    public $subject = '';
    public $content = '';
    public $document_type = '';
    public $document_to = '';
    public $signatories = [];
    public $users = [];
    public $types = [];

    public function fetchUsers()
    {
        $response = app(UserController::class)->index();
        $this->users = $response;
    }

    public function fetchDocumentTypes()
    {
        $response = app(DocumentTypeController::class)->index();
        $this->types = $response;
    }

    public function mount()
    {
        $this->signatories = [
            ['role' => '', 'user_id' => '']
        ];
        $this->users = [];
        $this->fetchUsers();
        $this->types = [];
        $this->fetchDocumentTypes();
    }

    public function addSignatory()
    {
        $this->signatories[] = ['role' => '', 'user_id' => ''];
    }

    public function removeSignatory($index)
    {
        unset($this->signatories[$index]);
        $this->signatories = array_values($this->signatories);
    }

    public function render()
    {
        return view('livewire.documents.create-document');
    }

    public function submitDocument($action)
    {
        // $this->validate([
        //     'subject' => 'required',
        //     'content' => 'required',
        // ]);

        // $status = $action === 'send' ? 'sent' : 'draft';

        // // Example save logic
        // Document::create([
        //     'subject' => $this->subject,
        //     'content' => $this->content,
        //     'status' => $status,
        // ]);

        // session()->flash('message', $status === 'sent' ? 'Document sent successfully!' : 'Draft saved.');
    }
}