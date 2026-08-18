<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentController;
use App\Mail\DocumentForReview;
use App\Mail\DocumentStatusUpdate;
use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Models\DocumentType;
use App\Models\ExternalDocument;
use App\Models\Office;
use App\Services\DocumentWorkflowService;
use App\Services\DocumentQueryService;
use App\Models\DocumentGenerationRule;
use App\Services\DocumentGenerationService;
use App\Services\AttachmentPreviewService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ViewDocument extends Component
{
    public $office_name;

    public $document;

    public $myStep;

    public $signatories;

    public $previewUrl;

    public $signed;

    public $rejected;

    public $display_text;

    public $document_query;

    public $selectedAttachment;

    public $attachmentPreviewUrl;
    public ?string $attachmentPreviewType = null;

    public $isSignatory;

    public $isCf;

    public $isRecipient;

    public $isRouting;

    public bool $canAct = false;

    public bool $canGenerate = false;
    public array $generationRules = [];

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

        $signatories = $this->document->steps->where('step_type', 'signatory')->map(function ($step) {
            return [
                'role' => $step->step_label,
                'user_name' => $step->signatory_name ?? $step->user?->name ?? '',
                'position' => $step->signatory_position ?? $step->user?->position ?? '',
                'signature' => $step->signature_path ?? '',
                'signed_for' => $step->signed_for,
                'signed' => $step->processed_at,
            ];
        });

        $cfs = $this->document->cfs->map(function ($signatory) {
            return [
                'role' => $signatory->signatory_label ?? 'Carbon Copy',
                'name' => $signatory->user->office->head->name ?? '',
                'office' => $signatory->user->office->name ?? '',
                'position' => $signatory->user->office->head->position ?? '',
                'signature' => $signatory->user->signature ?? '',
                'signed' => $signatory->signed_at ?? null,
            ];
        });

        $this->office_name = Auth::user()->office->name;

        if ($this->office_name != 'Records Section') {
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
            } else {
                if (! $this->document->viewed_at) {
                    $this->document->logs()->create([
                        'user_id' => Auth::id(),
                        'action' => 'Viewed',
                    ]);
                }
            }
        }

        $toPosition = $this->document->to_position ?? $this->document->toOffice?->head?->position ?? 'N/A';
        if ($toPosition !== 'University President' && $toPosition != 'N/A' && ! str_contains($toPosition, (string) $this->document->toOffice?->name)) {
            $toPosition .= ', '.$this->document->toOffice?->name;
        }

        $fromPosition = $this->document->from_position ?? $this->document->fromOffice->head->position ?? 'N/A';
        $fromLogo = $this->document->fromOffice->office_logo;
        if ($fromPosition !== 'University President' && $fromPosition != 'N/A' && ! str_contains($fromPosition, (string) $this->document->fromOffice?->name)) {
            $fromPosition .= ', '.$this->document->fromOffice->name;
        }

        $this->isSignatory = $this->document->steps
            ->where('step_type', 'signatory')
            ->contains(fn ($step) => $step->isAssignedTo(Auth::user()) || $step->user_id === Auth::id());
        $this->isRouting = $this->document->steps
            ->where('step_type', 'routing')
            ->contains(fn ($step) => $step->isAssignedTo(Auth::user()) || $step->user_id === Auth::id());
        $this->isCf = $this->document->cfs->contains('user_id', Auth::id()) || $this->isRouting;
        $this->isRecipient = $this->document->toOffice?->workflow_assignee?->is(Auth::user());

        $nextStep = $this->document->nextPendingStep();
        $this->canAct = in_array($this->document->status, ['Sent', 'In Process']) && $nextStep?->isAssignedTo(Auth::user());
        $generationStep = $this->document->steps
            ->first(fn ($step) => $step->step_type === 'action' && $step->status === 'Pending');
        $this->generationRules = app(DocumentGenerationService::class)->availableForInternal($this->document, Auth::user())->toArray();
        $this->canGenerate = $this->generationRules !== [];

        if (! $this->canAct && $this->myStep?->status === 'Pending' && $this->myStep->office?->acting_head_id) {
            $this->display_text = 'This step is currently assigned to the office OIC.';
        }

        $this->document_query = [
            'document' => $this->document->toJson(),
            'action' => 'Sent',
            'date_sent' => $this->document->date_sent,
            'subject' => $this->document->subject,
            'content' => $this->document->content,
            'thru' => $this->document->thru,
            'toName' => $this->document->to_name ?? $this->document->toOffice->head->name ?? $this->document->to_text,
            'toPosition' => $toPosition,
            'fromName' => $this->document->from_name ?? $this->document->fromOffice->head->name ?? 'N/A',
            'fromPosition' => $fromPosition,
            'office_logo' => $fromLogo,
            'documentType' => $this->document->documentType->name ?? 'N/A',
            'documentNumber' => $this->document->document_number,
            'unit' => $this->document->fromOffice->abbreviation,
            'signatories' => $signatories->toJson(),
            'cfs' => $cfs->toJson(),
            'attachments' => $this->document->attachments->toJson(),
        ];

        $key = uniqid();
        session([$key => $this->document_query]);
        $this->previewUrl = '/document/preview?'.$key;
    }

    public function sign()
    {
        $this->assertCurrentActorCanAct();

        $stepType = $this->myStep->step_type ?? 'signatory';

        $confirmText = match ($stepType) {
            'routing' => 'Set as reviewed',
            'action' => 'Complete Action',
            default => 'Sign!',
        };

        $requiresInput = in_array($stepType, ['routing', 'action']);

        $data = [
            'title' => 'Are you sure?',
            'text' => "You won't be able to revert this!",
            'icon' => 'warning',
            'input' => $requiresInput ? 'text' : null,
            'inputLabel' => $requiresInput ? 'Remarks' : null,
            'inputPlaceholder' => $requiresInput ? 'Enter your remarks here...' : null,
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
        $result = app(DocumentWorkflowService::class)->approve($this->document, Auth::user(), $remarks);
        $this->document = $result['document']->fresh();
        $event = $result['completed'] ? 'lastStep' : 'redirect';
        $mail_desc = $result['description'];

        if ($event != 'lastStep') {
            $recipientEmail = null;
            $recipientName = null;

            $nextStep = optional($this->document)->nextPendingStep();
            if ($nextStep && optional($nextStep->user)->email) {
                $recipientEmail = $nextStep->user->email;
                $recipientName = $nextStep->user->name;
            }

            if (! empty($recipientEmail)) {
                // Mail::to($recipientEmail)->send(new DocumentForReview($this->document, $recipientName ?? 'User'));
            }
        }

        $fromUser = optional($this->document->fromOffice->head);

        if (! empty($fromUser->email)) {
            // Mail::to($fromUser->email)->send(new DocumentStatusUpdate($this->document, $fromUser->name ?? 'User', 'signed', $mail_desc));
        }

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

    public function generate()
    {
        $rule = DocumentGenerationRule::whereHas('targetType', fn ($query) => $query->where('abbreviation', 'IOM'))->where('source_document_type_id', $this->document->document_type_id)->firstOrFail();
        return $this->generateDocument($rule->id);
    }

    public function generateSO()
    {
        $rule = DocumentGenerationRule::whereHas('targetType', fn ($query) => $query->where('abbreviation', 'SO'))->where('source_document_type_id', $this->document->document_type_id)->firstOrFail();
        return $this->generateDocument($rule->id);
    }

    public function generateDocument(int $ruleId)
    {
        $rule = DocumentGenerationRule::with('targetType', 'roles')->findOrFail($ruleId);
        session()->flash('redirect_data', app(DocumentGenerationService::class)->redirectData($rule, $this->document, Auth::user()));
        return redirect()->route('documents.create-document');
    }

    public function reject()
    {
        $this->assertCurrentActorCanAct();
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
        $result = app(DocumentWorkflowService::class)->reject($this->document, Auth::user(), $remarks);
        $this->document = $result['document']->fresh();
        $mail_desc = $result['description'];

        $fromUser = optional($this->document->fromOffice->head);

        if (! empty($fromUser->email)) {
            // Mail::to($fromUser->email)->send(new DocumentStatusUpdate($this->document, $fromUser->name ?? 'User', 'Rejected', $mail_desc));
        }

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

    private function assertCurrentActorCanAct(): void
    {
        $document = $this->document->fresh(['steps']);
        if (! in_array($document->status, ['Sent', 'In Process'])) {
            abort(403, 'This document is no longer awaiting action.');
        }

        $nextStep = $document->nextPendingStep();
        abort_unless($nextStep?->isAssignedTo(Auth::user()), 403, 'It is not your turn to act on this document.');
    }

    private function assertCanGenerate(string $target): void
    {
        abort_unless($this->document->status === 'Approved', 403, 'Only approved documents can produce an IOM or SO.');
        $source = $this->document->documentType?->abbreviation;
        $allowed = $target === 'IOM' ? in_array($source, ['RLM', 'IL'], true) : $source === 'IL';
        abort_unless($allowed, 403, 'This outcome is not allowed for this document type.');
        $generationStep = $this->document->steps()
            ->with('office.actingHead', 'office.head')
            ->where('step_type', 'action')
            ->where('status', 'Pending')
            ->first();
        abort_unless(
            $generationStep?->isAssignedTo(Auth::user()),
            403,
            'Only the office assigned to the generation step may create this document.',
        );
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

        $signatories = $attachment_document->steps->where('step_type', 'signatory')->map(function ($step) {
            return [
                'role' => $step->step_label,
                'user_name' => $step->signatory_name ?? $step->user?->name ?? '',
                'position' => $step->signatory_position ?? $step->user?->position ?? '',
                'signature' => $step->signature_path ?? '',
                'signed_for' => $step->signed_for,
                'signed' => $step->processed_at,
            ];
        });

        $toPosition = $attachment_document->to_position ?? $attachment_document->toOffice?->head?->position ?? 'N/A';
        if ($toPosition !== 'University President' && $toPosition != 'N/A' && ! str_contains($toPosition, (string) $attachment_document->toOffice?->name)) {
            $toPosition .= ', '.$attachment_document->toOffice?->name;
        }

        $fromPosition = $attachment_document->from_position ?? $attachment_document->fromOffice->head->position ?? 'N/A';
        $fromLogo = $attachment_document->fromOffice->office_logo;
        if ($fromPosition !== 'University President' && $fromPosition != 'N/A' && ! str_contains($fromPosition, (string) $attachment_document->fromOffice?->name)) {
            $fromPosition .= ', '.$attachment_document->fromOffice->name;
        }

        $attachment_query = [
            'document' => $attachment_document->toJson(),
            'action' => 'Sent',
            'date_sent' => $attachment_document->date_sent,
            'subject' => $attachment_document->subject,
            'content' => $attachment_document->content,
            'thru' => $attachment_document->thru,
            'toName' => $attachment_document->to_name ?? $attachment_document->toOffice->head->name ?? $attachment_document->to_text,
            'toPosition' => $toPosition,
            'fromName' => $attachment_document->from_name ?? $attachment_document->fromOffice->head->name ?? 'N/A',
            'fromPosition' => $fromPosition,
            'office_logo' => $fromLogo,
            'documentType' => $attachment_document->documentType->name ?? 'N/A',
            'documentNumber' => $attachment_document->document_number,
            'unit' => $attachment_document->fromOffice->abbreviation,
            'signatories' => $signatories->toJson(),
            'attachments' => $attachment_document->attachments->toJson(),
        ];

        return $attachment_query;
    }

    public function render()
    {
        return view('livewire.documents.view-document')->layout('layouts.app');
    }
}
