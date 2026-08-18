<?php

namespace App\Services;

use App\Models\Document;

class DocumentPreviewDataService
{
    public function build(Document $document, string $action = 'Sent'): array
    {
        $document->loadMissing([
            'fromOffice.head', 'toOffice.head', 'documentType', 'attachments',
            'steps.user', 'steps.office.head', 'cfs.user.office.head',
        ]);

        $toPosition = $document->to_position ?? $document->toOffice?->head?->position ?? 'N/A';
        $fromPosition = $document->from_position ?? $document->fromOffice?->head?->position ?? 'N/A';

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
            'fromPosition' => $document->fromOffice?->qualifyPosition($fromPosition) ?? $fromPosition,
            'issuingOfficeName' => $document->fromOffice?->name,
            'office_logo' => $document->fromOffice?->office_logo,
            'documentType' => $document->documentType?->name ?? 'N/A',
            'documentNumber' => $document->document_number,
            'unit' => $document->fromOffice?->abbreviation,
            'signatories' => $document->steps->where('step_type', 'signatory')->map(fn ($step) => [
                'role' => $step->step_label,
                'user_name' => $step->signatory_name ?? $step->user?->name ?? '',
                'position' => $step->signatory_position ?? $step->user?->position ?? '',
                'signature' => $step->signature_path ?? '',
                'signed_for' => $step->signed_for,
                'signed' => $step->processed_at,
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
