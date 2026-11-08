<?php

namespace App\Livewire\Documents;

use App\Http\Controllers\DocumentTypeController;
use App\Models\Document;
use App\Models\User;
use App\Support\DocumentNumber;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class ArchivePhysicalDocument extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $level = 'Inter';

    public string $documentTypeId = '';

    public bool $is_manual_document_number = false;

    public string $manual_document_sequence = '';

    public string $manual_document_year = '';

    public string $subject = '';

    public string $signedDate = '';

    public $scan;

    public string $selectedRecipient = '';

    #[Locked]
    public array $recipientIds = [];

    public function mount(): void
    {
        $this->authorizeArchive();
        $this->level = request()->query('level') === 'Intra' ? 'Intra' : 'Inter';
        $this->manual_document_year = date('Y');
    }

    public function addRecipient(): void
    {
        $this->authorizeArchive();
        $recipient = User::find($this->selectedRecipient);

        if (! $recipient?->office_id || ! $recipient->hasAccess('receive_documents')) {
            throw ValidationException::withMessages(['selectedRecipient' => 'Choose a user with access to Received Documents.']);
        }

        if (! in_array($recipient->id, $this->recipientIds, true)) {
            $this->recipientIds[] = $recipient->id;
        }

        $this->reset('selectedRecipient');
        $this->resetValidation('selectedRecipient');
    }

    public function removeRecipient(int $id): void
    {
        $this->recipientIds = array_values(array_diff($this->recipientIds, [$id]));
    }

    public function archive(): void
    {
        $this->authorizeArchive();
        $actor = Auth::user();

        $this->validate([
            'documentTypeId' => ['required', Rule::in($this->documentTypes->modelKeys())],
            'manual_document_sequence' => $this->is_manual_document_number ? 'required|integer|min:1' : 'nullable',
            'manual_document_year' => $this->is_manual_document_number ? 'required|digits:4|integer|min:1900|max:9999' : 'nullable',
            'subject' => ['required', 'string', 'max:255'],
            'signedDate' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'scan' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:12288'],
            'selectedRecipient' => ['prohibited'],
        ], ['selectedRecipient.prohibited' => 'Click Add CF to include the selected recipient.']);

        $documentNumber = 'PHY-'.Str::ulid();
        if ($this->is_manual_document_number) {
            $type = $this->documentTypes->find($this->documentTypeId);
            $documentNumber = DocumentNumber::format($type, $actor->office, $this->manual_document_sequence, $this->manual_document_year);
            Validator::make(
                ['manual_document_number' => $documentNumber],
                ['manual_document_number' => ['max:191', Rule::unique('documents', 'document_number')]],
                ['manual_document_number.unique' => 'This document number already exists. Choose a different number or year.'],
            )->validate();
        }

        $recipients = User::whereKey($this->recipientIds)->get();
        if ($recipients->count() !== count($this->recipientIds)
            || $recipients->contains(fn (User $recipient) => ! $recipient->office_id || ! $recipient->hasAccess('receive_documents'))) {
            throw ValidationException::withMessages(['selectedRecipient' => 'One or more recipients no longer have access to Received Documents.']);
        }

        $disk = Storage::disk('local');
        $path = 'physical-documents/'.$this->scan->hashName();
        $stream = $this->scan->readStream();

        try {
            if (! is_resource($stream) || ! $disk->put($path, $stream)) {
                throw ValidationException::withMessages(['scan' => 'The scan could not be saved. Please try again.']);
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        try {
            $document = DB::transaction(function () use ($actor, $path, $documentNumber) {
                $document = Document::create([
                    'document_number' => $documentNumber,
                    'from_id' => $actor->office_id,
                    'from_user_id' => $actor->id,
                    'document_type_id' => $this->documentTypeId,
                    'subject' => $this->subject,
                    'content' => '',
                    'created_by' => $actor->id,
                    'status' => 'Archived',
                    'date_sent' => $this->signedDate ?: null,
                    'file_url' => $path,
                    'document_level' => $this->level,
                    'is_physical' => true,
                ]);

                $document->cfs()->createMany(array_map(
                    fn (int $id) => ['user_id' => $id],
                    $this->recipientIds
                ));
                $document->logs()->create([
                    'user_id' => $actor->id,
                    'action' => 'Archived',
                    'description' => 'Uploaded a document signed on paper.',
                ]);

                return $document;
            });
        } catch (Throwable $exception) {
            $disk->delete($path);

            if ($exception instanceof UniqueConstraintViolationException && $this->is_manual_document_number) {
                throw ValidationException::withMessages(['manual_document_number' => 'This document number already exists. Choose a different number or year.']);
            }

            throw $exception;
        }

        $this->redirectRoute('documents.view-document', ['number' => $document->document_number]);
    }

    #[Computed]
    public function documentTypes()
    {
        return app(DocumentTypeController::class)->index(Auth::user())->where('document_level', $this->level);
    }

    private function authorizeArchive(): void
    {
        $user = Auth::user();
        abort_unless($user?->office_id && $user->hasAccess('send_documents'), 403);
    }

    public function render()
    {
        $users = User::with('office:id,abbreviation')->orderBy('name')->get(['id', 'name', 'office_id']);
        $selectedType = $this->is_manual_document_number ? $this->documentTypes->find($this->documentTypeId) : null;

        return view('livewire.documents.archive-physical-document', [
            'manualNumberParts' => $selectedType ? DocumentNumber::parts($selectedType, Auth::user()->office) : [],
            'recipientOptions' => $users->map(fn (User $user) => [
                'value' => (string) $user->id,
                'label' => $user->name.($user->office ? ' ('.$user->office->abbreviation.')' : ''),
            ])->all(),
            'recipients' => $users->whereIn('id', $this->recipientIds),
        ])->layout('layouts.app');
    }
}
