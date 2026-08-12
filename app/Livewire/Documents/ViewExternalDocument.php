<?php

namespace App\Livewire\Documents;

use App\Models\DocumentType;
use App\Models\ExternalDocument;
use App\Models\DocumentGenerationRule;
use App\Services\DocumentGenerationService;
use App\Services\AttachmentPreviewService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ViewExternalDocument extends Component
{
    public $previewUrl;
    public ?string $previewType = null;

    public $document;
    public array $generationRules = [];

    public function mount($id)
    {
        abort_unless(
            Auth::user()->hasAccess('receive_external_documents') || Auth::user()->hasAccess('send_external_documents'),
            403,
            'You do not have permission to access external documents.'
        );
        $this->document = ExternalDocument::with('toOffice.head', 'toOffice.actingHead')->findOrFail($id);

        $this->document->accessLogs()->firstOrCreate([
            'user_id' => Auth::id(),
            'action' => 'Viewed',
        ]);
        $this->previewType = app(AttachmentPreviewService::class)->previewType($this->document->file_url);
        $this->previewUrl = $this->previewType
            ? route('documents.external-document-preview', $this->document)
            : null;
        $this->generationRules = app(DocumentGenerationService::class)->availableForExternal($this->document, Auth::user())->toArray();
    }

    public function generateRLM()
    {
        abort_unless(Auth::user()->hasAccess('send_external_documents'), 403, 'You do not have permission to send external documents.');
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

    public function generateDocument(int $ruleId)
    {
        $rule = DocumentGenerationRule::with('targetType', 'roles')->findOrFail($ruleId);
        session()->flash('redirect_data', app(DocumentGenerationService::class)->redirectData($rule, $this->document, Auth::user()));
        return redirect()->route('documents.create-document');
    }

    public function generateECLR()
    {
        abort_unless(Auth::user()->hasAccess('send_external_documents'), 403, 'You do not have permission to send external documents.');
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
