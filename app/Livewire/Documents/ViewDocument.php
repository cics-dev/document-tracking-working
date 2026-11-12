<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentController;
use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Models\DocumentGenerationRule;
use App\Models\DocumentStep;
use App\Models\ExternalDocument;
use App\Services\AttachmentPreviewService;
use App\Services\DocumentGenerationService;
use App\Services\DocumentPreviewDataService;
use App\Services\DocumentQueryService;
use App\Services\DocumentWorkflowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ViewDocument extends Component
{
    public $document;

    public $myStep;

    public $previewUrl;

    public $signed;

    public $rejected;

    public $display_text;

    public $document_query;

    public $selectedAttachment;

    public $attachmentPreviewUrl;

    public ?string $attachmentPreviewType = null;

    public $isSignatory;

    public $isRouting;

    public bool $canAct = false;

    public bool $canGenerate = false;

    public bool $oicViewOnly = false;

    public array $generationRules = [];

    #[Locked]
    public ?int $signingStepId = null;

    #[Locked]
    public bool $rejectingSignature = false;

    public string $signingRemarks = '';

    public string $twoFactorCode = '';

    protected $listeners = ['documentSigned', 'documentRejected', 'lastStep'];

    public $showRemarks = true;

    public $remarksExpanded = false;

    public function minimizeRemarks()
    {
        $this->showRemarks = false;
    }

    public function mount($number)
    {
        abort_unless(app(DocumentQueryService::class)->canView(Auth::user(), $number), 403, 'You do not have permission to view this document.');
        $response = app(DocumentController::class)->getDocument($number);
        $this->document = $response;

        $this->document->accessLogs()->firstOrCreate([
            'user_id' => Auth::id(),
            'action' => 'Viewed',
        ]);

        if ($this->document->attachments->first()?->attachment_document_id) {
            $origDoc = Document::find($this->document->attachments[0]->attachment_document_id);
            $origDoc->steps()
                ->where('user_id', Auth::id())
                ->whereNull('viewed_at')
                ->update(['viewed_at' => now()]);
        }

        $updated = $this->document->cfs()
            ->where('user_id', Auth::id())
            ->whereNull('viewed_at')
            ->update(['viewed_at' => now()]);

        if ($updated) {
            $this->document->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'Viewed',
                'description' => Auth::user()->office->name.' viewed the document',
            ]);
        }

        if ($this->document->is_physical) {
            $this->previewUrl = route('documents.physical-scan', $this->document);

            return;
        }

        if ($this->document->steps->isNotEmpty() || $this->document->cfs->isNotEmpty()) {
            $this->myStep = $this->document->steps->first(fn ($step) => $step->isAssignedTo(Auth::user()))
                ?? $this->document->steps->firstWhere('user_id', Auth::id());
            if ($this->myStep) {
                $this->signed = ! empty($this->myStep->processed_at) && in_array($this->myStep->status, ['Approved', 'Reviewed']);
                $this->rejected = ! empty($this->myStep->processed_at) && in_array($this->myStep->status, ['Rejected', 'Returned']);

                if ($this->signed) {
                    $this->display_text = match ($this->myStep->step_type) {
                        'signatory' => 'You have already signed this document.',
                        'action' => 'You have already processed/generated this document.',
                        default => 'You have already reviewed this document.',
                    };
                } elseif ($this->rejected) {
                    $this->display_text = match ($this->myStep->step_type) {
                        'signatory' => 'You have rejected this document.',
                        'action' => 'You have rejected/returned this document.',
                        default => 'You have returned this document.',
                    };
                }

                if (is_null($this->myStep->viewed_at)) {
                    $this->myStep->viewed_at = now();
                    $this->myStep->save();
                    $this->document->logs()->create([
                        'user_id' => Auth::id(),
                        'action' => 'Viewed',
                        'description' => $this->myStep->user->office->name.' viewed the document',
                    ]);
                }
            }
        } elseif (! $this->document->viewed_at) {
            $this->document->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'Viewed',
            ]);
        }

        $this->isSignatory = $this->document->steps
            ->where('step_type', 'signatory')
            ->contains(fn ($step) => $step->isAssignedTo(Auth::user()) || $step->user_id === Auth::id());
        $this->isRouting = $this->document->steps
            ->where('step_type', 'routing')
            ->contains(fn ($step) => $step->isAssignedTo(Auth::user()) || $step->user_id === Auth::id());
        $nextStep = $this->document->nextPendingStep();
        $this->canAct = in_array($this->document->status, ['Sent', 'In Process']) && $nextStep?->isAssignedTo(Auth::user());
        $this->generationRules = app(DocumentGenerationService::class)->availableForInternal($this->document, Auth::user())->toArray();
        $this->canGenerate = $this->generationRules !== [];
        $this->oicViewOnly = ! $this->canAct && ! $this->canGenerate
            && in_array($this->document->status, ['Sent', 'In Process', 'Approved'], true)
            && $nextStep?->office?->head_id === Auth::id()
            && $nextStep->office->actingHead !== null
            && $nextStep->office->acting_head_id !== Auth::id()
            && $nextStep->isAssignedTo($nextStep->office->actingHead);

        if (! $this->canAct && $this->myStep?->status === 'Pending' && $this->myStep->office?->acting_head_id) {
            $this->display_text = ($this->document->documentType?->allow_oic_signature ?? true)
                ? 'This step is currently assigned to the office OIC.'
                : 'This document type requires the designated office head to sign.';
        }

        $this->document_query = app(DocumentPreviewDataService::class)->build($this->document);

        $key = uniqid();
        session([$key => $this->document_query]);
        $this->previewUrl = '/document/preview?'.$key;
    }

    public function sign()
    {
        $step = $this->assertCurrentActorCanAct();

        if ($this->showSigningChallenge($step, rejecting: false)) {
            return;
        }

        $stepType = $this->myStep->step_type ?? 'signatory';

        $confirmText = match ($stepType) {
            'routing' => 'Set as reviewed',
            'action' => 'Complete Action',
            default => 'Sign!',
        };

        $data = [
            'title' => 'Are you sure?',
            'text' => "You won't be able to revert this!",
            'icon' => 'warning',
            'input' => 'text',
            'inputLabel' => 'Remarks',
            'inputPlaceholder' => 'Enter your remarks here...',
            'showCancelButton' => true,
            'confirmButtonColor' => '#d33',
            'cancelButtonColor' => '#3085d6',
            'confirmButtonText' => $confirmText,
            'event' => 'documentSigned',
            'withId' => false,
        ];

        $this->dispatch('fireSwal', $data);
    }

    public function documentSigned($remarks = null)
    {
        $this->approveDocument($remarks);
    }

    private function approveDocument(?string $remarks, ?string $code = null, ?int $stepId = null): void
    {
        $result = app(DocumentWorkflowService::class)->approve($this->document, Auth::user(), $remarks, $code, $stepId);
        $this->document = $result['document']->fresh();
        $event = $result['completed'] ? 'lastStep' : 'redirect';

        $data = [
            'title' => 'Document processed!',
            'text' => "You've successfully processed the document",
            'icon' => 'success',
            'showCancelButton' => false,
            'confirmButtonColor' => '#d33',
            'cancelButtonColor' => '#3085d6',
            'confirmButtonText' => 'Okay',
            'event' => $event,
            'withId' => false,
            'url' => url('/documents/received'),
        ];

        $this->dispatch('fireSwal', $data);
    }

    public function lastStep()
    {
        $this->document->refresh();
        abort_if($this->document->is_physical || str_starts_with($this->document->status, 'Generated '), 403);

        $isComplete = $this->document->steps()
            ->whereIn('step_type', ['routing', 'signatory'])
            ->where(function ($query) {
                $query->whereNull('processed_at')
                    ->orWhereNotIn('status', ['Approved', 'Reviewed']);
            })
            ->doesntExist();

        abort_unless($isComplete, 403, 'This document is not ready for final approval.');

        $this->document->update([
            'status' => 'Approved',
        ]);

        return redirect()->route('documents.list-documents', ['mode' => 'received']);
    }

    public function generateDocument(int $ruleId)
    {
        abort_if($this->document->is_physical, 403);

        $rule = DocumentGenerationRule::with('targetType', 'roles')->findOrFail($ruleId);
        session()->flash('redirect_data', app(DocumentGenerationService::class)->redirectData($rule, $this->document, Auth::user()));

        return redirect()->route('documents.create-document');
    }

    public function reject()
    {
        $step = $this->assertCurrentActorCanAct();

        if ($this->showSigningChallenge($step, rejecting: true)) {
            return;
        }

        $stepType = $this->myStep->step_type ?? 'signatory';

        $confirmText = match ($stepType) {
            'routing' => 'Return with remarks',
            'action' => 'Reject Action',
            default => 'Reject',
        };

        $data = [
            'title' => 'Are you sure?',
            'text' => 'Please confirm and optionally leave a remark.',
            'icon' => 'warning',
            'input' => 'text',
            'inputLabel' => 'Remarks',
            'inputPlaceholder' => 'Enter your remarks here...',
            'showCancelButton' => true,
            'confirmButtonColor' => '#d33',
            'cancelButtonColor' => '#3085d6',
            'confirmButtonText' => $confirmText,
            'event' => 'documentRejected',
            'withId' => false,
        ];
        $this->dispatch('fireSwal', $data);
    }

    public function documentRejected($remarks)
    {
        $this->rejectDocument($remarks);
    }

    private function rejectDocument(?string $remarks, ?string $code = null, ?int $stepId = null): void
    {
        $result = app(DocumentWorkflowService::class)->reject($this->document, Auth::user(), $remarks, $code, $stepId);
        $this->document = $result['document']->fresh();

        $data = [
            'title' => 'Document rejected!',
            'text' => "You've successfully rejected the document",
            'icon' => 'success',
            'showCancelButton' => false,
            'confirmButtonColor' => '#d33',
            'cancelButtonColor' => '#3085d6',
            'confirmButtonText' => 'Okay',
            'event' => 'redirect',
            'withId' => false,
            'url' => url('/documents/received'),
        ];

        $this->dispatch('fireSwal', $data);
    }

    private function showSigningChallenge(DocumentStep $step, bool $rejecting): bool
    {
        if ($step->step_type !== 'signatory' || ! Auth::user()->require_two_factor_for_signing) {
            return false;
        }

        $this->reset('signingRemarks', 'twoFactorCode');
        $this->resetValidation(['twoFactorCode', 'document']);
        $this->signingStepId = $step->id;
        $this->rejectingSignature = $rejecting;
        $this->modal('verify-signature')->show();

        return true;
    }

    public function confirmSignature(): void
    {
        abort_if($this->signingStepId === null, 403);

        $code = $this->pull('twoFactorCode');

        if ($this->rejectingSignature) {
            $this->rejectDocument($this->signingRemarks, $code, $this->signingStepId);
        } else {
            $this->approveDocument($this->signingRemarks, $code, $this->signingStepId);
        }

        $this->reset('signingStepId', 'rejectingSignature', 'signingRemarks');
        $this->modal('verify-signature')->close();
    }

    private function assertCurrentActorCanAct(): DocumentStep
    {
        $document = $this->document->fresh(['steps']);
        if (! in_array($document->status, ['Sent', 'In Process'])) {
            abort(403, 'This document is no longer awaiting action.');
        }

        $nextStep = $document->nextPendingStep();
        abort_unless($nextStep?->isAssignedTo(Auth::user()), 403, 'It is not your turn to act on this document.');

        return $nextStep;
    }

    public function viewAttachment($id, $type)
    {
        if ($type == 'internal') {
            $this->selectedAttachment = DocumentAttachment::find($id);
        } elseif ($type == 'external') {
            $this->selectedAttachment = ExternalDocument::find($id);
        }

        if (! $this->selectedAttachment->is_upload && $type == 'internal') {
            $attachment_document = $this->selectedAttachment->attachmentDocument;
            $attachment_query = $this->processPDF($attachment_document);
            $key = uniqid();
            session([$key => $attachment_query]);
            $this->attachmentPreviewUrl = '/document/preview?'.$key;
            $this->attachmentPreviewType = 'pdf';
        } else {
            $this->attachmentPreviewType = app(AttachmentPreviewService::class)->previewType($this->selectedAttachment->file_url);
            $this->attachmentPreviewUrl = $type === 'external'
                ? route('documents.external-document-preview', $this->selectedAttachment)
                : route('documents.attachment-preview', $this->selectedAttachment);
        }

        $this->modal('view-attachment-modal')->show();
    }

    public function processPDF($attachment_document)
    {
        $response = app(DocumentController::class)->getDocument($attachment_document->document_number);
        $attachment_document = $response;

        $updated = $attachment_document->cfs()
            ->where('user_id', Auth::id())
            ->whereNull('viewed_at')
            ->update(['viewed_at' => now()]);

        if ($updated) {
            $attachment_document->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'Viewed',
                'description' => Auth::user()->office->name.' viewed the document',
            ]);
        }

        return app(DocumentPreviewDataService::class)->build($attachment_document);
    }

    public function render()
    {
        return view('livewire.documents.view-document')->layout('layouts.app');
    }
}
