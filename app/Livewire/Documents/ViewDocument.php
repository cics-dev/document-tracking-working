<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentController;
use App\Models\DocumentType;
use App\Models\Document;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

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
        $this->office_name = Auth::user()->office->name;

        if ($this->office_name != 'Administration' && $this->office_name != 'Records Section') {
            if ($this->document->routings->isNotEmpty() || $this->document->signatories->isNotEmpty()) {
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

        // dd($this->document->attachments()->latest()->first()->file_url);
    
        // dd($this->document->attachments[0]->file_url);
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
            'signatories' => $signatories->toJson(),
            'attachments' => $this->document->attachments->toJson(),
        ];

        $this->previewUrl = '/document/preview?' . http_build_query($this->document_query);
    }

    public function sign()
    {        
        if ($this->mySignatory != null && ($this->document->document_type_id != 2 && auth()->user()->position != 'University President'))
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
        $this->document->status = 'Approved';
        $this->document->save();

        if ($this->document->document_type_id == 2 && $this->document->attachments()->latest()->first() != null) {
            $attachmentDetails = Document::find($this->document->attachments()->latest()->first()->attachment_document_id);
            $docSignatories = $attachmentDetails->signatories->sortBy('sequence');
            $lastSignatory = $docSignatories->last();
            $lastSignatory->signed_at = now();
            $lastSignatory->save();
            $attachmentDetails->status = 'Approved';
            $attachmentDetails->save();
            $attachmentDetails->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'signed',
                'description' => $lastSignatory->user->office->name . ' signed the document'
            ]);

            $fromPosition =  $attachmentDetails->fromOffice->head->position ?? 'N/A';
            $fromLogo =  $attachmentDetails->fromOffice->office_logo;
            if ($fromPosition !== 'University President' && $fromPosition != 'N/A') {
                $fromPosition .= ', ' . $attachmentDetails->fromOffice->name;
            }
            $toPosition =  $attachmentDetails->toOffice->head->position ?? 'N/A';
            if ($toPosition !== 'University President' && $toPosition != 'N/A') {
                $toPosition .= ', ' . $attachmentDetails->toOffice->name;
            }

            $this->document_query = [
                'document' => $attachmentDetails->toJson(),
                'action' => 'sent',
                'date_sent' => $attachmentDetails->date_sent,
                'subject' => $attachmentDetails->subject,
                'content' => $attachmentDetails->content,
                'thru' => $attachmentDetails->thru,
                'toName' => $attachmentDetails->toOffice->head->name ?? 'N/A',
                'toPosition' => $toPosition,
                'fromName' => $attachmentDetails->fromOffice->head->name ?? 'N/A',
                'fromPosition' => $fromPosition,
                'office_logo' => $fromLogo,
                'documentType' => $attachmentDetails->documentType->name ?? 'N/A',
                'documentNumber' => $attachmentDetails->document_number,
                'signatories' => $attachmentDetails->signatories->map(function ($signatory) {
                    return [
                        'role' => $signatory->signatory_label,
                        'user_name' => $signatory->user->office->head->name ?? '',
                        'position' => $signatory->user->office->head->position ?? '',
                        'signature' => $signatory->user->signature ?? '',
                        'signed' => $signatory->signed_at,
                    ];
                })->toJson(),
                'attachment' => $attachmentDetails->attachments()->latest()->first()?->file_url
            ];
        
            $this->document_query['date_sent'] = $this->document_query['date_sent'] ?? now();
            $this->document_query['attachment'] = $this->document_query['attachment'] ?? null;

            if (isset($this->document_query['signatories']) && is_string($this->document_query['signatories'])) {
                $this->document_query['signatories'] = json_decode($this->document_query['signatories'], true);
            }
            if (isset($this->document_query['cfs']) && is_string($this->document_query['cfs'])) {
                $this->document_query['cfs'] = json_decode($this->document_query['cfs'], true);
            }

            $pdf = Pdf::loadView('pdf.document-preview', $this->document_query)->setPaper([0, 0, 612.00, 936.00]);

            $filename = 'document_' . $attachmentDetails->document_number . '.pdf';
            Storage::disk('public')->put('assets/files/' . $filename, $pdf->output());
        }
        
        return redirect()->route('documents.list-documents', ['mode' => 'received']);
    }

    public function generate()
    {
        // if ($this->document->status == 'pending') {
            $this->document->status = 'Generated IOM';
            $this->document->save();
            
            $this->document_query['date_sent'] = $this->document_query['date_sent'] ?? now();
            $this->document_query['attachment'] = $this->document_query['attachment'] ?? null;

            if (isset($this->document_query['signatories']) && is_string($this->document_query['signatories'])) {
                $this->document_query['signatories'] = json_decode($this->document_query['signatories'], true);
            }
            if (isset($this->document_query['cfs']) && is_string($this->document_query['cfs'])) {
                $this->document_query['cfs'] = json_decode($this->document_query['cfs'], true);
            }

            $pdf = Pdf::loadView('pdf.document-preview', $this->document_query)->setPaper([0, 0, 612.00, 936.00]);

            $filename = 'document_' . $this->document->document_number . '.pdf';
            Storage::disk('public')->put('assets/files/' . $filename, $pdf->output());
            // $this->document->attachments()->create([
            //     'document_number'=>$this->document->document_number,
            //     'attachment_document_id'=>$this->document->id,
            //     'status'=>'Waiting for Signature',
            //     'file_url'=>'assets/files/' . $filename,
            // ]);
            $filename = 'assets/files/' . $filename;
            $this->document->update([
                'file_url'=>$filename
            ]);

        // }
        // else {
        //     $filename = $this->document->attachments()->latest()->first()->file_url;
        // }

        session()->flash('redirect_data', [
            'to' => $this->document->fromOffice->id,
            'from' => $this->document->toOffice->id,
            'subject' => 'RE: ' . $this->document->subject,
            'original_document_id' => $this->document->id,
            'document_type_id' => DocumentType::where('abbreviation', 'IOM')->value('id'),
            'content' => '<p>Pursuant to the approved-request letter memorandum (<b>' . $this->document->document_number . '</b>)',
            'thru' => null,
            'cf' => $this->document->signatories
                ->map(fn($s) => $s->user->office->id ?? null)
                // ->filter(fn($id) => $id !== Office::where('name', 'Office of the University President')->value('id'))
                ->unique()
                ->values()
                ->merge([Office::where('name', 'Records Section')->value('id'), Auth::user()->office->id])
                ->toArray(),
            'attachment' => $filename
        ]);        

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

    public function render()
    {
        return view('livewire.documents.view-document');
    }
}
