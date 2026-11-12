<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentController;
use App\Services\DocumentQueryService;
use App\Models\Document;
use App\Models\DocumentTrackingShare;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TrackDocument extends Component
{
    public $document;

    public string $shareExpiresIn = '168';

    #[Locked]
    public string $publicShareUrl = '';

    #[Locked]
    public ?int $publicShareId = null;

    public function mount($number)
    {
        abort_unless(app(DocumentQueryService::class)->canView(Auth::user(), $number), 403, 'You do not have permission to track this document.');
        $response = app(DocumentController::class)->getDocument($number);
        $this->document = $response;
    }

    private function authorizedShareDocument(): Document
    {
        $document = $this->document->fresh();
        abort_unless($document && Auth::user() && filled($document->document_number)
            && app(DocumentQueryService::class)->canView(Auth::user(), $document->document_number), 403);
        abort_if($document->status === 'Draft' || ! filled($document->document_number), 403);

        return $document;
    }

    public function createPublicShare(): void
    {
        $this->validate(['shareExpiresIn' => ['required', Rule::in(['1', '24', '168'])]]);
        $document = $this->authorizedShareDocument();
        $token = bin2hex(random_bytes(32));
        $share = DocumentTrackingShare::create([
            'document_id' => $document->id, 'created_by' => Auth::id(),
            'token_hash' => hash('sha256', $token), 'expires_at' => now()->addHours((int) $this->shareExpiresIn),
        ]);
        $this->publicShareId = $share->id;
        $this->publicShareUrl = route('tracking.public', ['token' => $token]);
    }

    public function revokePublicShare(int $shareId): void
    {
        $document = $this->authorizedShareDocument();
        $share = DocumentTrackingShare::whereKey($shareId)->where('document_id', $document->id)
            ->where('created_by', Auth::id())->firstOrFail();
        $share->update(['revoked_at' => now()]);
        if ($this->publicShareId === $share->id) {
            $this->publicShareUrl = '';
            $this->publicShareId = null;
        }
    }

    public function render()
    {
        return view('livewire.documents.track-document', [
            'trackingShares' => DocumentTrackingShare::where('document_id', $this->document->id)
                ->where('created_by', Auth::id())->whereNull('revoked_at')->where('expires_at', '>', now())
                ->latest('id')->get(['id', 'created_at', 'expires_at']),
        ])->layout('layouts.app');
    }
}
