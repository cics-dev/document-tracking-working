<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use App\Models\ExternalDocument;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReceiveExternalDocument extends Component
{
    use WithFileUploads;

    #[Validate('required|file|max:102400')] // Max 100MB
    public $attachment;

    #[Validate('required|string|max:255')]
    public $document_from;

    #[Validate('required|exists:offices,id')]
    public $document_to_id = '';

    #[Validate('required|string|max:255')]
    public $subject;

    // We don't need a public property for received_date if it's always now()
    // If you need it to be editable, add a public property.

    /**
     * Computed property to fetch offices.
     * Accessible in view via $this->offices
     */
    #[Computed]
    public function offices()
    {
        // Optimized: Only select necessary columns and sort
        return Office::select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    public function removeAttachment()
    {
        $this->attachment = null;
    }
    
    public function cancel()
    {
        return redirect()->route('documents.list-external-documents');
    }

    public function submitDocument()
    {
        $this->validate();

        $path = null;
        $fileType = null;

        if ($this->attachment) {
            $path = $this->attachment->store('attachments', 'public');
            $fileType = $this->attachment->getClientOriginalExtension();
        }

        $year = now()->year;
        $prefix = 'EC';

        $document_number = DB::transaction(function () use ($year, $prefix) {

            // Get the latest record for this specific year
            $latestDoc = ExternalDocument::whereYear('created_at', $year)
                ->where('document_number', 'LIKE', "$prefix-%-$year") // Ensure we match the format
                ->lockForUpdate() // Lock rows to prevent race conditions
                ->orderBy('id', 'desc') // Assuming ID is auto-increment, otherwise rely on created_at or a specific sequence column
                ->first();

            if ($latestDoc) {
                // Explode the string "EC-5-2025" to get "5"
                // Format is: Prefix-Sequence-Year
                $parts = explode('-', $latestDoc->document_number);
                
                // Safety check: ensure we actually have 3 parts
                if (count($parts) === 3) {
                    $nextSequence = (int)$parts[1] + 1;
                } else {
                    // Fallback if format is messed up
                    $nextSequence = 1;
                }
            } else {
                // No documents found for this year, start at 1
                $nextSequence = 1;
            }

            $newDocNumber = "$prefix-$nextSequence-$year";

            return $newDocNumber;
        });

        ExternalDocument::create([
            'document_number' => $document_number,
            'from' => $this->document_from,
            'to_id' => $this->document_to_id,
            'subject' => $this->subject,
            'received_date' => now(), // Or use a date picker input if backdating is allowed
            'file_url' => $path,
            'file_type' => $fileType,
            'created_by' => Auth::id(), // Good practice to track who encoded it
        ]);

        session()->flash('success', 'External Document received successfully.');

        return redirect()->route('documents.list-external-documents');
    }

    public function render()
    {
        return view('livewire.documents.receive-external-document');
    }
}