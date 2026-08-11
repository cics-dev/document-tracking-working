<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentController;
use App\Models\DocumentType;
use App\Models\DocumentAttachment;
use App\Models\Document;
use App\Models\ExternalDocument;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentForReview;
use App\Mail\DocumentStatusUpdate;

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
    public $isSignatory;
    public $isCf;
    public $isRecipient;
    public $isRouting;
    public bool $canAct = false;

    protected $listeners = ['documentSigned', 'documentRejected', 'lastStep'];

    public $showRemarks = true;
    public $remarksExpanded = false;

    public function minimizeRemarks()
    {
        $this->showRemarks = false;
    }

    public function mount($number)
    {
        $response = app(DocumentController::class)->getDocument($number);
        $this->document = $response;

        $this->document->accessLogs()->firstOrCreate([
            'user_id' => Auth::id(),
            'action' => 'Viewed',
        ]);

        if ($this->document->document_type_id == 2 && $this->document->attachments->first()?->attachment_document_id) {
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
                'description' => Auth::user()->office->name . ' viewed the document'
            ]);
        }
        
        $signatories = $this->document->steps->where('step_type', 'signatory')->map(function ($step) {
            return [
                'role' => $step->step_label,
                'user_name' => $step->user->office->head->name ?? '',
                'position' => $step->user->office->head->position ?? '',
                'signature' => $step->user->signature ?? '',
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

        if ($this->office_name != 'Administration' && $this->office_name != 'Records Section') {
            if ($this->document->steps->isNotEmpty() || $this->document->cfs->isNotEmpty()) {
                $this->myStep = $this->document->steps->firstWhere('user_id', Auth::user()->id);
                if ($this->myStep) {
                    $this->signed = !empty($this->myStep->processed_at) && in_array($this->myStep->status, ['Approved', 'Reviewed']);
                    $this->rejected = !empty($this->myStep->processed_at) && in_array($this->myStep->status, ['Rejected', 'Returned']);

                    if ($this->signed) {
                        $this->display_text = match($this->myStep->step_type) {
                            'signatory' => 'You have already signed this document.',
                            'action' => 'You have already processed/generated this document.',
                            default => 'You have already reviewed this document.',
                        };
                    } elseif ($this->rejected) {
                        $this->display_text = match($this->myStep->step_type) {
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
                            'description' => $this->myStep->user->office->name . ' viewed the document'
                        ]);
                    }
                }
            } else {
                if (!$this->document->viewed_at) {
                    $this->document->logs()->create([
                        'user_id' => Auth::id(),
                        'action' => 'Viewed'
                    ]);
                }
            }
        }

        $toPosition = $this->document->toOffice?->head?->position ?? 'N/A';
        if ($toPosition !== 'University President' && $toPosition != 'N/A') {
            $toPosition .= ', ' . $this->document->toOffice?->name;
        }

        $fromPosition = $this->document->fromOffice->head->position ?? 'N/A';
        $fromLogo = $this->document->fromOffice->office_logo;
        if ($fromPosition !== 'University President' && $fromPosition != 'N/A') {
            $fromPosition .= ', ' . $this->document->fromOffice->name;
        }

        $this->isSignatory = $this->document->steps->where('step_type', 'signatory')->contains('user_id', Auth::id());
        $this->isRouting = $this->document->steps->where('step_type', 'routing')->contains('user_id', Auth::id());
        $this->isCf = $this->document->cfs->contains('user_id', Auth::id()) || $this->isRouting;
        $this->isRecipient = $this->document->toOffice?->head_id == Auth::id();

        $nextStep = $this->document->nextPendingStep();
        $this->canAct = in_array($this->document->status, ['Sent', 'In Process']) && $nextStep && $nextStep->user_id === Auth::id();

        $this->document_query = [
            'document' => $this->document->toJson(),
            'action' => 'Sent',
            'date_sent' => $this->document->date_sent,
            'subject' => $this->document->subject,
            'content' => $this->document->content,
            'thru' => $this->document->thru,
            'toName' => $this->document->toOffice->head->name ?? $this->document->to_text,
            'toPosition' => $toPosition,
            'fromName' => $this->document->fromOffice->head->name ?? 'N/A',
            'fromPosition' => $fromPosition,
            'office_logo' => $fromLogo,
            'documentType' => $this->document->document_level == 'Intra' ? 'Intra' : ($this->document->documentType->name ?? 'N/A'),
            'documentNumber' => $this->document->document_number,
            'unit' => $this->document->fromOffice->abbreviation,
            'signatories' => $signatories->toJson(),
            'cfs' => $cfs->toJson(),
            'attachments' => $this->document->attachments->toJson(),
        ];

        $key = uniqid();
        session([$key => $this->document_query]);
        $this->previewUrl = '/document/preview?' . $key;
    }

    public function sign()
    {        
        $this->assertCurrentActorCanAct();
        
        $stepType = $this->myStep->step_type ?? 'signatory';

        $confirmText = match($stepType) {
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
        $this->assertCurrentActorCanAct();
        $mail_desc = '';

        if ($this->myStep) {
            $stepType = $this->myStep->step_type;
            
            $statusMap = [
                'routing' => 'Reviewed',
                'action' => 'Approved',
                'signatory' => 'Approved',
            ];

            $this->myStep->processed_at = now();
            $this->myStep->comments = $remarks;
            $this->myStep->status = $statusMap[$stepType] ?? 'Approved';
            $this->myStep->save();

            // Check if all steps with type 'routing' or 'signatory' are completed
            $isComplete = $this->document->fresh()->steps()
                ->whereIn('step_type', ['routing', 'signatory'])
                ->where(function ($query) {
                    $query->whereNull('processed_at')
                          ->orWhereNotIn('status', ['Approved', 'Reviewed']);
                })
                ->doesntExist();

            if ($isComplete) {
                $this->document->update(['status' => 'Approved']);
                $event = 'lastStep';
            } else {
                if ($this->document->status === 'Sent') {
                    $this->document->update(['status' => 'In Process']);
                }
                $event = 'redirect';
            }

            $actionDescMap = [
                'routing' => ' reviewed the document',
                'action' => ' completed action on the document',
                'signatory' => ' signed the document',
            ];
            $mail_desc = $this->myStep->user->office->name . ($actionDescMap[$stepType] ?? ' signed the document');
            
            $this->document->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'signed',
                'description' => $mail_desc
            ]);
        } else {
            $event = 'redirect';
        }

        if ($event != 'lastStep') {
            $recipientEmail = null;
            $recipientName = null;

            $nextStep = optional($this->document)->nextPendingStep();
            if ($nextStep && optional($nextStep->user)->email) {
                $recipientEmail = $nextStep->user->email;
                $recipientName = $nextStep->user->name;
            }

            if (!empty($recipientEmail)) {
                // Mail::to($recipientEmail)->send(new DocumentForReview($this->document, $recipientName ?? 'User'));
            }
        }

        $fromUser = optional($this->document->fromOffice->head);

        if (!empty($fromUser->email)) {
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
            'url' => url('/documents/received')
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
            'status' => 'Approved'
        ]);

        return redirect()->route('documents.list-documents', ['mode' => 'received']);
    }

    public function generate()
    {
        $this->assertCanGenerate('IOM');

        $redirectData = [
            'to' => $this->document->fromOffice->id,
            'from' => $this->document->toOffice->id ?? 1,
            'subject' => 'RE: ' . $this->document->subject,
            'original_document_id' => $this->document->id,
            'document_type_id' => DocumentType::where('abbreviation', 'IOM')->value('id'),
            'document_type' => 'IOM',
            'content' => '<p>Pursuant to the approved-request letter memorandum (<b>' . $this->document->document_number . '</b>)',
            'thru' => null,
        ];

        $signatorySteps = $this->document->steps->where('step_type', 'signatory');
        if ($signatorySteps->isNotEmpty()) {
            $recordsSectionId = Office::where('name', 'Records Section')->value('id');
            
            $redirectData['cf'] = $signatorySteps
                ->pluck('user.office.id')
                ->push($this->document->from_id)
                ->push($recordsSectionId)
                ->push(Auth::user()->office?->id)
                ->flatten()
                ->filter() 
                ->unique() 
                ->values() 
                ->toArray();
        }

        session()->flash('redirect_data', $redirectData);
        return redirect()->route('documents.create-document');
    }

    public function generateSO()
    {
        $this->assertCanGenerate('SO');
        $redirectData = [
            'to' => $this->document->from_id,
            'from' => $this->document->toOffice->id ?? 1,
            'subject' => 'RE: ' . $this->document->subject,
            'original_document_id' => $this->document->id,
            'document_type_id' => DocumentType::where('abbreviation', 'SO')->value('id'),
            'document_type' => 'SO',
        ];

        session()->flash('redirect_data', $redirectData);
        return redirect()->route('documents.create-document');
    }

    public function reject()
    {
        $this->assertCurrentActorCanAct();
        $stepType = $this->myStep->step_type ?? 'signatory';

        $confirmText = match($stepType) {
            'routing' => 'Return with remarks',
            'action' => 'Reject Action',
            default => 'Reject',
        };

        $data = [
            'title' => 'Are you sure?',
            'text' => "Please confirm and optionally leave a remark.",
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
        $this->assertCurrentActorCanAct();
        $mail_desc = '';

        if ($this->myStep) {
            $stepType = $this->myStep->step_type;
            
            $statusMap = [
                'routing' => 'Returned',
                'action' => 'Rejected',
                'signatory' => 'Rejected',
            ];

            $newStatus = $statusMap[$stepType] ?? 'Rejected';

            $this->myStep->update([
                'processed_at' => now(),
                'comments' => $remarks,
                'status' => $newStatus
            ]);

            $actionDescMap = [
                'routing' => ' returned the document with remarks: ',
                'action' => ' rejected action on the document with remarks: ',
                'signatory' => ' rejected the document with remarks: ',
            ];

            $mail_desc = $this->myStep->user->office->name . ($actionDescMap[$stepType] ?? ' rejected the document with remarks: ') . $remarks;
            
            $this->document->logs()->create([
                'user_id' => Auth::id(),
                'action' => $newStatus,
                'description' => $mail_desc
            ]);

            $documentStatusMap = [
                'routing' => 'Returned',
                'action' => 'Rejected',
                'signatory' => 'Rejected',
            ];
            
            $this->document->status = $documentStatusMap[$stepType] ?? 'Rejected';
            $this->document->save();
        }

        $fromUser = optional($this->document->fromOffice->head);

        if (!empty($fromUser->email)) {
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
            'url' => url('/documents/received')
        ];
        
        $this->dispatch('fireSwal', $data);
    }

    private function assertCurrentActorCanAct(): void
    {
        $document = $this->document->fresh(['steps']);
        if (!in_array($document->status, ['Sent', 'In Process'])) {
            abort(403, 'This document is no longer awaiting action.');
        }

        $nextStep = $document->nextPendingStep();
        abort_unless($nextStep && $nextStep->user_id === Auth::id(), 403, 'It is not your turn to act on this document.');
    }

    private function assertCanGenerate(string $target): void
    {
        abort_unless(in_array(Auth::user()->office?->id, [18, 28], true), 403);
        abort_unless($this->document->status === 'Approved', 403, 'Only approved documents can produce an IOM or SO.');
        $source = $this->document->documentType?->abbreviation;
        $allowed = $target === 'IOM' ? in_array($source, ['RLM', 'IL'], true) : $source === 'IL';
        abort_unless($allowed, 403, 'This outcome is not allowed for this document type.');
    }

    public function viewAttachment($id, $type) {
        if ($type == 'internal') {
            $this->selectedAttachment = DocumentAttachment::find($id);
        } else if ($type == 'external') {
            $this->selectedAttachment = ExternalDocument::find($id);
        }
        
        if (!$this->selectedAttachment->is_upload && $type == 'internal') {
            $attachment_document = $this->selectedAttachment->attachmentDocument;
            $attachment_query = $this->processPDF($attachment_document);
            $key = uniqid();
            session([$key => $attachment_query]);
            $this->attachmentPreviewUrl = '/document/preview?' . $key;
        } else {
            $this->attachmentPreviewUrl = asset('storage/' . $this->selectedAttachment->file_url);
        }

        $this->modal('view-attachment-modal')->show();
    }

    public function processPDF($attachment_document) {
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
                'description' => Auth::user()->office->name . ' viewed the document'
            ]);
        }
        
        $signatories = $attachment_document->steps->where('step_type', 'signatory')->map(function ($step) {
            return [
                'role' => $step->step_label,
                'user_name' => $step->user->office->head->name ?? '',
                'position' => $step->user->office->head->position ?? '',
                'signature' => $step->user->signature ?? '',
                'signed' => $step->processed_at,
            ];
        });

        $toPosition = $attachment_document->toOffice?->head?->position ?? 'N/A';
        if ($toPosition !== 'University President' && $toPosition != 'N/A') {
            $toPosition .= ', ' . $attachment_document->toOffice?->name;
        }

        $fromPosition = $attachment_document->fromOffice->head->position ?? 'N/A';
        $fromLogo = $attachment_document->fromOffice->office_logo;
        if ($fromPosition !== 'University President' && $fromPosition != 'N/A') {
            $fromPosition .= ', ' . $attachment_document->fromOffice->name;
        }

        $attachment_query = [
            'document' => $attachment_document->toJson(),
            'action' => 'Sent',
            'date_sent' => $attachment_document->date_sent,
            'subject' => $attachment_document->subject,
            'content' => $attachment_document->content,
            'thru' => $attachment_document->thru,
            'toName' => $attachment_document->toOffice->head->name ?? $attachment_document->to_text,
            'toPosition' => $toPosition,
            'fromName' => $attachment_document->fromOffice->head->name ?? 'N/A',
            'fromPosition' => $fromPosition,
            'office_logo' => $fromLogo,
            'documentType' => $attachment_document->document_level == 'Intra' ? 'Intra' : ($attachment_document->documentType->name ?? 'N/A'),
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