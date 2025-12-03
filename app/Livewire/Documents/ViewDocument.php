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

class ViewDocument extends Component
{
    public $office_name;
    public $document;
    public $mySignatory;
    public $myReview;
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

    protected $listeners = ['documentSigned', 'documentRejected', 'lastSignatory'];

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
            'action' => 'viewed',
        ]);

        if ($this->document->document_type_id == 2 && $this->document->attachments[0]->attachment_document_id) {
            $origDoc = Document::find($this->document->attachments[0]->attachment_document_id);
            $origDoc->signatories()
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
                'action' => 'viewed',
                'description' => Auth::user()->office->name . ' viewed the document'
            ]);
        }
        
        $signatories = $this->document->signatories->map(function ($signatory) {
            return [
                'role' => $signatory->signatory_label,
                'user_name' => $signatory->user->office->head->name ?? '',
                'position' => $signatory->user->office->head->position ?? '',
                'signature' => $signatory->user->signature ?? '',
                'signed' => $signatory->signed_at,
            ];
        });
        $cfs = $this->document->cfs->map(function ($signatory) {
            return [
                'role' => $signatory->signatory_label,
                'name' => $signatory->user->office->head->name ?? '',
                'office' => $signatory->user->office->name ?? '',
                'position' => $signatory->user->office->head->position ?? '',
                'signature' => $signatory->user->signature ?? '',
                'signed' => $signatory->signed_at,
            ];
        });
        $this->office_name = Auth::user()->office->name;

        if ($this->office_name != 'Administration' && $this->office_name != 'Records Section') {
            if ($this->document->routings->isNotEmpty() || $this->document->signatories->isNotEmpty() || $this->document->cfs->isNotEmpty()) {
                $this->mySignatory = $this->document->signatories->firstWhere('user_id', Auth::user()->id);
                $this->myReview = $this->document->routings->firstWhere('user_id', Auth::user()->id);
                if ($this->mySignatory) {
                    $this->signed = $this->mySignatory->signed_at;
                    $this->rejected = $this->mySignatory->rejected_at;

                    if ($this->signed) {
                        $this->display_text = 'You have already signed this document.';
                    } elseif ($this->rejected) {
                        $this->display_text = 'You have rejected this document.';
                    }

                    if (is_null($this->mySignatory->viewed_at)) {
                        $this->mySignatory->viewed_at = now();
                        $this->mySignatory->save();
                        $this->document->logs()->create([
                            'user_id' => Auth::id(),
                            'action' => 'viewed',
                            'description' => $this->mySignatory->user->office->name . ' viewed the document'
                        ]);
                    }
                }
                else if ($this->myReview) {
                    $this->signed = $this->myReview->reviewed_at;
                    $this->rejected = $this->myReview->returned_at;

                    if ($this->signed) {
                        $this->display_text = 'You have already reviewed this document.';
                    } elseif ($this->rejected) {
                        $this->display_text = 'You have returned this document.';
                    }

                    if (is_null($this->myReview->viewed_at)) {
                        $this->myReview->viewed_at = now();
                        $this->myReview->save();
                        $this->document->logs()->create([
                            'user_id' => Auth::id(),
                            'action' => 'viewed',
                            'description' => $this->myReview->user->office->name . ' viewed the document'
                        ]);
                    }
                }
            }
            else {
                if (!$this->document->viewed_at) {
                    $this->document->logs()->create([
                        'user_id' => Auth::id(),
                        'action' => 'viewed'
                    ]);
                }
            }
        }

        $toPosition = $this->document->toOffice->head->position ?? 'N/A';
        if ($toPosition !== 'University President' && $toPosition != 'N/A') {
            $toPosition .= ', ' . $this->document->toOffice->name;
        }

        $fromPosition = $this->document->fromOffice->head->position ?? 'N/A';
        $fromLogo = $this->document->fromOffice->office_logo;
        if ($fromPosition !== 'University President' && $fromPosition != 'N/A') {
            $fromPosition .= ', ' . $this->document->fromOffice->name;
        }

        $this->isSignatory = $this->document->signatories->contains('user_id', Auth::id());

        $this->isRouting = $this->document->routings->contains('user_id', Auth::id());

        $this->isCf = $this->document->cfs->contains('user_id', Auth::id()) || $this->isRouting;

        $this->isRecipient = $this->document->toOffice?->head_id == Auth::id();


        $this->document_query = [
            'document' => $this->document->toJson(),
            'action' => 'sent',
            'date_sent' => $this->document->date_sent,
            'subject' => $this->document->subject,
            'content' => $this->document->content,
            'thru' => $this->document->thru,
            'toName' => $this->document->toOffice->head->name ?? $this->document->to_text,
            'toPosition' => $toPosition,
            'fromName' => $this->document->fromOffice->head->name ?? 'N/A',
            'fromPosition' => $fromPosition,
            'office_logo' => $fromLogo,
            'documentType' => $this->document->document_level=='Intra'?'Intra':($this->document->documentType->name ?? 'N/A'),
            'documentNumber' => $this->document->document_number,
            'unit' => $this->document->fromOffice->abbreviation,
            'signatories' => $signatories->toJson(),
            'cfs' => $cfs->toJson(),
            'attachments' => $this->document->attachments->toJson(),
        ];


        // $this->previewUrl = '/document/preview?' . http_build_query($this->document_query);

        $key = uniqid();
        session([$key => $this->document_query]);
        $this->previewUrl = '/document/preview?' . $key;
    }

    public function sign()
    {        
        if ($this->mySignatory != null)
            $data = [
                'title' => 'Are you sure?',
                'text' => "You won't be able to revert this!",
                'icon' => 'warning',
                'showCancelButton' => true,
                'confirmButtonColor' => '#d33',
                'cancelButtonColor' => '#3085d6',
                'confirmButtonText' => 'Sign!',
                'event' => 'documentSigned',
                'withId' => false,
            ];
        else
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
                'confirmButtonText' => 'Set as reviewed',
                'event' => 'documentSigned',
                'withId' => false,
            ];
        
        $this->dispatch('fireSwal', $data);
    }

    public function documentSigned($remarks = null)
    {
        if ($this->mySignatory) {
            $this->signatories = $this->document->signatories->sortBy('sequence');
            $lastSignatory = $this->signatories->last();

            if ($lastSignatory->user_id === Auth::id()) $event = 'lastSignatory';
            else $event = 'redirect';

            $this->mySignatory->signed_at = now();
            $this->mySignatory->save();
            $this->document->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'signed',
                'description' => $this->mySignatory->user->office->name . ' signed the document'
            ]);
        }
        else if ($this->document->document_type_id == 2 && auth()->user()->position == 'University President') {
            $event = 'lastSignatory';

            $this->document->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'signed',
                'description' => auth()->user()->office->name . ' signed the document'
            ]);
        }
        else if ($this->myReview) {
            $event = 'redirect';

            $this->myReview->reviewed_at = now();
            $this->myReview->comments = $remarks;
            $this->myReview->save();
            $this->document->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'signed',
                'description' => $this->myReview->user->office->name . ' approved the document'
            ]);
        }

        if ($event != 'lastSignatory') {

            $recipientEmail = null;
            $recipientName = null;

            // SAFE routing query
            $firstRoute = optional($this->document)
                ->routings()
                ->whereNull('reviewed_at')
                ->whereNull('returned_at')
                ->orderBy('id', 'asc')
                ->first();

            // Check routing user + email safely
            if ($firstRoute && optional($firstRoute->user)->email) {
                $recipientEmail = $firstRoute->user->email;
                $recipientName = $firstRoute->user->name;
            } else {

                // SAFE signatory query
                $firstSignatory = optional($this->document)
                    ->signatories()
                    ->whereNull('signed_at')
                    ->whereNull('rejected_at')
                    ->orderBy('sequence', 'asc')
                    ->first();

                // Check signatory user + email safely
                if ($firstSignatory && optional($firstSignatory->user)->email) {
                    $recipientEmail = $firstSignatory->user->email;
                    $recipientName = $firstSignatory->user->name;
                }
            }

            // Send email only if valid
            if (!empty($recipientEmail)) {
                Mail::to($recipientEmail)->send(
                    new DocumentForReview($this->document, $recipientName ?? 'User')
                );
            }
        }


        $data = [
            'title' => 'Document signed!',
            'text' => "You've successfully signed the document",
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

    public function lastSignatory()
    {
        $this->document->update([
            'status' => 'Approved'
        ]);

        $this->document->signatories[0]->signed_at = now();
        $this->document->signatories[0]->save();

        if ($this->document->document_type_id == 2 && $this->document->attachments()->latest()->first() != null) {
            $attachmentDetails = Document::find($this->document->attachments()->latest()->first()->attachment_document_id);
            $docSignatories = $attachmentDetails->signatories->sortBy('sequence');
            $lastSignatory = $docSignatories->last();
            $lastSignatory->signed_at = now();
            $lastSignatory->save();
            $attachmentDetails->update([
                'status' => 'Approved'
            ]);
            $attachmentDetails->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'signed',
                'description' => $lastSignatory->user->office->name . ' signed the document'
            ]);
        }
        
        return redirect()->route('documents.list-documents', ['mode' => 'received']);
    }

    public function generate()
    {
        // if ($this->document->status == 'pending') {
            // $this->document->status = 'Generated IOM';
            // $this->document->save();
            
            // $this->document_query['date_sent'] = $this->document_query['date_sent'] ?? now();
            // $this->document_query['attachment'] = $this->document_query['attachment'] ?? null;

            // if (isset($this->document_query['signatories']) && is_string($this->document_query['signatories'])) {
            //     $this->document_query['signatories'] = json_decode($this->document_query['signatories'], true);
            // }
            // if (isset($this->document_query['cfs']) && is_string($this->document_query['cfs'])) {
            //     $this->document_query['cfs'] = json_decode($this->document_query['cfs'], true);
            // }

            // $pdf = Pdf::loadView('pdf.document-preview', $this->document_query)->setPaper([0, 0, 612.00, 936.00]);

            // $filename = 'document_' . $this->document->document_number . '.pdf';
            // Storage::disk('public')->put('assets/files/' . $filename, $pdf->output());
            // $this->document->attachments()->create([
            //     'document_number'=>$this->document->document_number,
            //     'attachment_document_id'=>$this->document->id,
            //     'status'=>'Waiting for Signature',
            //     'file_url'=>'assets/files/' . $filename,
            // ]);
            // $filename = 'assets/files/' . $filename;
            // $this->document->update([
            //     'file_url'=>$filename
            // ]);

        // }
        // else {
        //     $filename = $this->document->attachments()->latest()->first()->file_url;
        // }

        $redirectData = [
            'to' => $this->document->fromOffice->id,
            'from' => $this->document->toOffice->id??1,
            'subject' => 'RE: ' . $this->document->subject,
            'original_document_id' => $this->document->id,
            'document_type_id' => DocumentType::where('abbreviation', 'IOM')->value('id'),
            'document_type' => 'IOM',
            'content' => '<p>Pursuant to the approved-request letter memorandum (<b>' . $this->document->document_number . '</b>)',
            'thru' => null,
        ];

        // ✅ Add 'cf' only if there are signatories
        if ($this->document->signatories->isNotEmpty()) {
            if ($this->document->signatories?->isNotEmpty()) {
                $recordsSectionId = Office::where('name', 'Records Section')->value('id');
                
                $redirectData['cf'] = $this->document->signatories
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
        }

        session()->flash('redirect_data', $redirectData);
    
        return redirect()->route('documents.create-document');
    }

    public function generateSO()
    {
        $redirectData = [
            'from' => $this->document->toOffice->id??1,
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
            'confirmButtonText' => $this->mySignatory != null? 'Reject':'Return with remarks',
            'event' => 'documentRejected',
            'withId' => false,
        ];
        $this->dispatch('fireSwal', $data);
    }

    public function documentRejected($remarks)
    {
        if ($this->mySignatory){
            $this->mySignatory->update([
                'rejected_at' => now(),
                'comments' => $remarks
            ]);
            $this->document->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'rejected',
                'description' => $this->mySignatory->user->office->name . ' rejected the document with remarks: '. $remarks
            ]);
            $this->document->status ='Rejected';
            $this->document->save();
            $this->mySignatory->save();
        }
        else if ($this->myReview){
            $this->myReview->update([
                'returned_at' => now(),
                'comments' => $remarks
            ]);
            $this->document->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'returned',
                'description' => $this->myReview->user->office->name . ' returned the document with remarks: '. $remarks
            ]);
            $this->document->status ='Rejected';
            $this->document->save();
            $this->myReview->save();
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

    public function viewAttachment($id, $type) {
        if ($type == 'internal') {
            $this->selectedAttachment = DocumentAttachment::find($id);
        }
        else if ($type == 'external') {
            $this->selectedAttachment = ExternalDocument::find($id);
        }
        // dd($type);
        if (!$this->selectedAttachment->is_upload && $type =='internal') {
            $attachment_document = $this->selectedAttachment->attachmentDocument;
            $attachment_query = $this->processPDF($attachment_document);
            // $this->attachmentPreviewUrl = '/document/preview?' . http_build_query($attachment_query);
            $key = uniqid();
            session([$key => $attachment_query]);
            $this->attachmentPreviewUrl = '/document/preview?' . $key;
        }
        else {
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
                'action' => 'viewed',
                'description' => Auth::user()->office->name . ' viewed the document'
            ]);
        }
        
        $signatories = $attachment_document->signatories->map(function ($signatory) {
            return [
                'role' => $signatory->signatory_label,
                'user_name' => $signatory->user->office->head->name ?? '',
                'position' => $signatory->user->office->head->position ?? '',
                'signature' => $signatory->user->signature ?? '',
                'signed' => $signatory->signed_at,
            ];
        });

        $toPosition = $attachment_document->toOffice->head->position ?? 'N/A';
        if ($toPosition !== 'University President' && $toPosition != 'N/A') {
            $toPosition .= ', ' . $attachment_document->toOffice->name;
        }

        $fromPosition = $attachment_document->fromOffice->head->position ?? 'N/A';
        $fromLogo = $attachment_document->fromOffice->office_logo;
        if ($fromPosition !== 'University President' && $fromPosition != 'N/A') {
            $fromPosition .= ', ' . $attachment_document->fromOffice->name;
        }

        // dd($attachment_document->attachments()->latest()->first()->file_url);
    
        // dd($attachment_document->attachments[0]->file_url);
        $attachment_query = [
            'document' => $attachment_document->toJson(),
            'action' => 'sent',
            'date_sent' => $attachment_document->date_sent,
            'subject' => $attachment_document->subject,
            'content' => $attachment_document->content,
            'thru' => $attachment_document->thru,
            'toName' => $attachment_document->toOffice->head->name ?? $attachment_document->to_text,
            'toPosition' => $toPosition,
            'fromName' => $attachment_document->fromOffice->head->name ?? 'N/A',
            'fromPosition' => $fromPosition,
            'office_logo' => $fromLogo,
            'documentType' => $attachment_document->document_level=='Intra'?'Intra':($attachment_document->documentType->name ?? 'N/A'),
            'documentNumber' => $attachment_document->document_number,
            'unit' => $attachment_document->fromOffice->abbreviation,
            'signatories' => $signatories->toJson(),
            'attachments' => $attachment_document->attachments->toJson(),
        ];
        
        return $attachment_query;
    }

    public function render()
    {
        return view('livewire.documents.view-document');
    }
}
