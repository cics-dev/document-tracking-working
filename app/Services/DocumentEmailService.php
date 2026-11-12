<?php

namespace App\Services;

use App\Mail\DocumentForReview;
use App\Mail\DocumentStatusUpdate;
use App\Models\Document;
use App\Models\Office;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

class DocumentEmailService
{
    public function sent(Document $document): void
    {
        if (! config('document-notifications.email_enabled')) {
            return;
        }
        $this->attempt(fn () => $this->notifyNextStep($document));
    }

    public function processed(array $result): void
    {
        if (! config('document-notifications.email_enabled')) {
            return;
        }
        $this->attempt(function () use ($result) {
            $document = $result['document'];
            $final = in_array($document->status, ['Approved', 'Rejected', 'Returned'], true);
            if (! in_array($document->status, ['Rejected', 'Returned'], true)) {
                $this->attempt(fn () => $this->notifyNextStep($document));
            }
            $offices = collect([$document->fromOffice]);
            if ($final) {
                $offices = $offices->merge($document->attachments()
                    ->where('is_upload', false)->whereNotNull('attachment_document_id')
                    ->with('attachmentDocument.fromOffice.head', 'attachmentDocument.fromOffice.actingHead')
                    ->get()->map(fn ($attachment) => $attachment->attachmentDocument?->fromOffice));
            }
            $action = strtolower($final ? $document->status : $result['step']->status);
            foreach ($this->recipients($offices) as $recipient) {
                $this->attempt(fn () => Mail::to($recipient->email)->send(new DocumentStatusUpdate(
                    $document, $recipient->name, $action, $result['description'],
                )));
            }
        });
    }

    private function notifyNextStep(Document $document): void
    {
        $step = $document->nextPendingStep();
        if (! $step) {
            return;
        }
        foreach ($this->recipients(collect([$step->office])) as $recipient) {
            $this->attempt(fn () => Mail::to($recipient->email)->send(new DocumentForReview($document, $recipient->name)));
        }
    }

    private function attempt(callable $notification): void
    {
        try {
            $notification();
        } catch (Throwable $exception) {
            // Notification failures must never interrupt a document action.
            try {
                Log::warning('Document email skipped after a notification failure.', [
                    'exception_type' => $exception::class,
                ]);
            } catch (Throwable) {
                // Even an unavailable logger must not surface an email error.
            }
        }
    }

    private function recipients(Collection $offices): Collection
    {
        return $offices->filter()->unique('id')->flatMap(function (Office $office) {
            return [$office->head, $office->actingHead];
        })->filter(fn ($user) => $user && ! $user->trashed() && filled($user->email))
            ->unique(fn ($user) => mb_strtolower(trim($user->email)))->values();
    }
}
