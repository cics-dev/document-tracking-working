<?php

namespace App\Livewire\Documents;

use App\Models\DocumentGenerationRule;
use App\Models\ExternalDocument;
use App\Services\AttachmentPreviewService;
use App\Services\DocumentGenerationService;
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

    public function generateDocument(int $ruleId)
    {
        $rule = DocumentGenerationRule::with('targetType', 'roles')->findOrFail($ruleId);
        session()->flash('redirect_data', app(DocumentGenerationService::class)->redirectData($rule, $this->document, Auth::user()));

        return redirect()->route('documents.create-document');
    }

    public function render()
    {
        return view('livewire.documents.view-external-document')->layout('layouts.app');
    }
}
