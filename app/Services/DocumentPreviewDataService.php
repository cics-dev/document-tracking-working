<?php

namespace App\Services;

use App\Models\Document;

class DocumentPreviewDataService
{
    public function build(Document $document, string $action = 'Sent'): array
    {
        $document->loadMissing([
            'fromOffice.head', 'fromOffice.actingHead', 'fromOffice.users', 'sender',
            'toOffice.head', 'documentType', 'attachments',
            'steps.user', 'steps.office.head', 'cfs.user.office.head',
        ]);

        $toPosition = $document->to_position ?? $document->toOffice?->head?->position ?? 'N/A';
        $fromPosition = $document->from_position ?? $document->fromOffice?->head?->position ?? 'N/A';
        $loggedSender = $document->logs()->with('user')->where('action', 'Sent')->latest('id')->first()?->user;
        $namedSender = $document->fromOffice?->users->first(
            fn ($user) => strcasecmp($user->name, (string) $document->from_name) === 0
        );
        $namedSenderIsHead = $namedSender?->is($document->fromOffice?->head) ?? false;
        $allowOicSignature = $document->documentType?->allow_oic_signature ?? true;
        $policySender = $namedSenderIsHead
            ? ($allowOicSignature ? $document->fromOffice?->actingHead : $namedSender)
            : null;
        $sender = $policySender
            ?? $document->sender
            ?? $loggedSender
            ?? $namedSender
            ?? $document->fromOffice?->workflow_assignee;
        $fromSignedFor = $sender && filled($document->from_name)
            && strcasecmp($sender->name, $document->from_name) !== 0;

        return [
            'document' => $document->toJson(),
            'action' => $action,
            'date_sent' => $document->date_sent,
            'subject' => $document->subject,
            'content' => $document->content,
            'thru' => $document->thru,
            'toName' => $document->to_name ?? $document->toOffice?->head?->name ?? $document->to_text,
            'toPosition' => $document->toOffice?->qualifyPosition($toPosition) ?? $toPosition,
            'fromName' => $document->from_name ?? $document->fromOffice?->head?->name ?? 'N/A',
            'fromSignature' => $sender?->signature,
            'fromSignedFor' => $fromSignedFor,
            'fromPosition' => $document->fromOffice?->qualifyPosition($fromPosition) ?? $fromPosition,
            'issuingOfficeName' => $document->fromOffice?->name,
            'office_logo' => $document->fromOffice?->office_logo,
            'documentType' => $document->documentType?->name ?? 'N/A',
            'printLayout' => $document->documentType?->print_layout ?? 'memorandum',
            'senderSignaturePolicy' => $document->documentType?->sender_signature_policy ?? 'approved',
            'approverDisplayMode' => $document->documentType?->approver_display_mode ?? 'labeled',
            'documentNumber' => $document->document_number,
            'unit' => $document->fromOffice?->abbreviation,
            'signatories' => $document->steps->where('step_type', 'signatory')->map(fn ($step) => [
                'role' => $step->step_label,
                'user_name' => $step->signatory_name ?? $step->user?->name ?? '',
                'position' => $step->signatory_position ?? $step->user?->position ?? '',
                'signature' => $step->signature_path ?? '',
                'signed_for' => $step->signed_for,
                'signed' => $step->processed_at,
                'status' => $step->status,
                'comments' => $step->comments,
            ])->values()->toJson(),
            'cfs' => $document->cfs->map(fn ($copy) => [
                'role' => 'Carbon Copy',
                'name' => $copy->user?->office?->head?->name ?? '',
                'office' => $copy->user?->office?->name ?? '',
                'position' => $copy->user?->office?->head?->position ?? '',
                'signature' => $copy->user?->signature ?? '',
                'signed' => null,
            ])->values()->toJson(),
            'attachments' => $document->attachments->toJson(),
        ];
    }
}
