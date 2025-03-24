<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentController;
use App\Models\DocumentType;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ViewDocument extends Component
{
    public $office_name;
    public $document;
    public $mySignatory;
    public $signatories;
    public $previewUrl;
    public $signed;
    public $rejected;
    public $display_text;

    protected $listeners = ['documentSigned', 'documentRejected', 'lastSignatory'];

    public function mount($number)
    {
        $response = app(DocumentController::class)->getDocument($number);
        $this->document = $response;
        
        $signatories = $this->document->signatories->map(function ($signatory) {
            return [
                'role' => $signatory->signatory_label,
                'user_name' => $signatory->user->office->head->name ?? '',
                'position' => $signatory->user->office->head->position ?? '',
            ];
        });
        $this->office_name = Auth::user()->office->name;

        if ($this->office_name != 'Administration' && $this->office_name != 'Records Section') {

            $this->mySignatory = $this->document->signatories->firstWhere('user_id', Auth::user()->id);

            if (!$this->mySignatory) {
                abort(403, 'Access denied.');
                return;
            }

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
                    'action' => $this->mySignatory->user->office->name . ' viewed the document'
                ]);
            }
        }

        $toPosition = $this->document->toOffice->head->position ?? 'N/A';
        if ($toPosition !== 'University President' && $toPosition != 'N/A') {
            $toPosition .= ', ' . $this->document->toOffice->name;
        }

        $fromPosition = $this->document->fromOffice->head->position ?? 'N/A';
        if ($fromPosition !== 'University President' && $fromPosition != 'N/A') {
            $fromPosition .= ', ' . $this->document->fromOffice->name;
        }
    
        $query = http_build_query([
            'action' => 'sent',
            'date_sent' => $this->document->date_sent,
            'subject' => $this->document->subject,
            'content' => $this->document->content,
            'toName' => $this->document->toOffice->head->name ?? 'N/A',
            'toPosition' => $toPosition,
            'fromName' => $this->document->fromOffice->head->name ?? 'N/A',
            'fromPosition' => $fromPosition,
            'documentType' => $this->document->documentType->name ?? 'N/A',
            'documentNumber' => $this->document->document_number,
            'signatories' => $signatories->toJson(),
        ]);
    
        // $this->dispatch('open-preview-tab', [
        //     'url' => '/document/preview?' . $query
        // ]);

        $this->previewUrl = '/document/preview?' . $query;
    }

    public function sign()
    {        
        $data = [
            'title' => 'Are you sure?',
            'text' => "You won't be able to revert this!",
            'icon' => 'warning',
            'showCancelButton' => true,
            'confirmButtonColor' => '#d33',
            'cancelButtonColor' => '#3085d6',
            'confirmButtonText' => 'Yes, sign it!',
            'event' => 'documentSigned',
            'withId' => false,
        ];
        
        $this->dispatch('fireSwal', $data);
    }

    public function documentSigned()
    {
        $this->signatories = $this->document->signatories->sortBy('sequence');
        $lastSignatory = $this->signatories->last();

        if ($lastSignatory->user_id === Auth::id()) $event = 'lastSignatory';
        else $event = 'redirect';




        $this->mySignatory->signed_at = now();
        $this->mySignatory->save();
        $this->document->logs()->create([
            'user_id' => Auth::id(),
            'action' => $this->mySignatory->user->office->name . ' signed the document'
        ]);
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
        // session()->flash('redirect_data', [
        //     'to' => $this->document->fromOffice->id,
        //     'subject' => 'RE: ' . $this->document->subject,
        //     'document_type_id' => DocumentType::where('abbreviation', 'IOM')->value('id'),
        //     'content' => '<p>Pursuant to the approved-request letter memorandum (<b>' . $this->document->document_number . '</b>)',
        //     'cf' => $this->signatories
        //     ->map(fn($s) => $s->user->office->id ?? null) // Get office_id via user
        //     ->filter(fn($id) => $id !== Auth::user()->office->id) // Exclude current user's office
        //     ->unique()
        //     ->values()
        //     ->toArray(),
        // ]);        

        // return redirect()->route('documents.create-document');
        return redirect()->route('documents.list-documents', ['mode' => 'received']);
    }

    public function generate()
    {
        session()->flash('redirect_data', [
            'to' => $this->document->fromOffice->id,
            'subject' => 'RE: ' . $this->document->subject,
            'document_type_id' => DocumentType::where('abbreviation', 'IOM')->value('id'),
            'content' => '<p>Pursuant to the approved-request letter memorandum (<b>' . $this->document->document_number . '</b>)',
            'cf' => $this->document->signatories
                ->map(fn($s) => $s->user->office->id ?? null)
                ->filter(fn($id) => $id !== Office::where('name', 'Office of the University President')->value('id'))
                ->unique()
                ->values()
                ->merge([Office::where('name', 'Records Section')->value('id'), Auth::user()->office->id])
                ->toArray(),
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
            'confirmButtonText' => 'Reject it!',
            'event' => 'documentRejected',
            'withId' => false,
        ];
        $this->dispatch('fireSwal', $data);
    }

    public function documentRejected($remarks)
    {
        $this->mySignatory->update([
            'rejected_at' => now(),
            'comments' => $remarks
        ]);
        $this->document->logs()->create([
            'user_id' => Auth::id(),
            'action' => $this->mySignatory->user->office->name . ' rejected the document with remarks: '. $remarks
        ]);
        $this->mySignatory->save();
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
