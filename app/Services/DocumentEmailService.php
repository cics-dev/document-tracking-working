<?php

namespace App\Services;

use App\Mail\DocumentForReview;
use App\Mail\DocumentStatusUpdate;
use App\Models\Document;
use App\Models\Office;
use App\Models\User;
use Illuminate\Mail\Mailable;
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
            $recipients = $this->recipients($offices);
            if ($recipients->isEmpty()) {
                $this->log('warning', 'Document status email skipped: no active office recipient with an email address.', ['document_id' => $document->id]);
            }
            foreach ($recipients as $recipient) {
                $this->deliver($document, $recipient, new DocumentStatusUpdate(
                    $document, $recipient->name, $action, $result['description'],
                ));
            }
        });
    }

    private function notifyNextStep(Document $document): void
    {
        $step = $document->nextPendingStep();
        if (! $step) {
            return;
        }
        $recipients = $this->recipients(collect([$step->office]));
        if ($recipients->isEmpty()) {
            $this->log('warning', 'Document first/next-step email skipped: no active office recipient with an email address.', [
                'document_id' => $document->id, 'step_id' => $step->id, 'office_id' => $step->office_id,
            ]);
        }
        foreach ($recipients as $recipient) {
            $this->deliver($document, $recipient, new DocumentForReview($document, $recipient->name));
        }
    }

    private function deliver(Document $document, User $recipient, Mailable $mail): void
    {
        $context = [
            'document_id' => $document->id,
            'recipient_user_id' => $recipient->id,
            'notification' => class_basename($mail),
        ];
        try {
            $sent = Mail::to($recipient->email)->send($mail);
        } catch (Throwable $exception) {
            $this->log('warning', 'Document email skipped after a delivery failure.', $context + [
                'exception_type' => $exception::class,
                'reason' => $exception->getMessage(),
            ]);
            return;
        }
        if ($sent) {
            $this->log('info', 'Document email accepted by mail transport.', $context + [
                'message_id' => $sent->getMessageId(),
            ]);
        } else {
            $this->log('warning', 'Document email was not accepted by mail transport.', $context);
        }
    }

    private function log(string $level, string $message, array $context): void
    {
        try {
            Log::log($level, $message, $context);
        } catch (Throwable) {
            // Logging must not interrupt document actions or other recipients.
        }
    }

    private function attempt(callable $notification): void
    {
        try {
            $notification();
        } catch (Throwable $exception) {
            $this->log('warning', 'Document email skipped after a notification failure.', [
                'exception_type' => $exception::class,
                'reason' => $exception->getMessage(),
            ]);
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
