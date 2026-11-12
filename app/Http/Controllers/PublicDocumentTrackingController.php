<?php

namespace App\Http\Controllers;

use App\Models\DocumentTrackingShare;
use Illuminate\Http\Response;

class PublicDocumentTrackingController extends Controller
{
    public function __invoke(string $token): Response
    {
        $share = DocumentTrackingShare::where('token_hash', hash('sha256', $token))
            ->whereNull('revoked_at')->where('expires_at', '>', now())->firstOrFail();
        // Only explicitly allowed fields enter the public view. No Livewire model
        // snapshot, subject, content, people, attachments, remarks or logs are sent.
        $document = $share->document()->select('id', 'document_number', 'created_at', 'document_type_id', 'status')
            ->where('status', '!=', 'Draft')->firstOrFail();
        $document->load(['documentType:id,name', 'steps' => fn ($query) => $query
            ->select('id', 'document_id', 'office_id', 'step_label', 'status', 'processed_at', 'sequence')
            ->with('office:id,abbreviation')->orderBy('sequence')->orderBy('id')]);
        $steps = $document->steps->map(fn ($step) => [
            'office' => $step->office?->abbreviation ?? 'Unassigned',
            'label' => $step->step_label,
            'status' => $step->status,
            'completedAt' => $step->processed_at?->timezone('Asia/Manila')->format('M d, Y h:i A'),
        ]);
        $progress = $steps->isEmpty() ? 0 : (int) round($steps->whereNotNull('completedAt')->count() / $steps->count() * 100);
        if (in_array($document->status, ['Approved', 'Completed'], true)) $progress = 100;
        $tracking = [
            'reference' => $document->document_number,
            'created' => $document->created_at->timezone('Asia/Manila')->format('M d, Y'),
            'type' => $document->documentType?->name ?? 'Document',
            'status' => $document->status,
            'progress' => $progress,
            'steps' => $steps->all(),
        ];

        return response()->view('public.document-tracking', ['tracking' => $tracking])->withHeaders([
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; frame-ancestors 'none'; base-uri 'none'; form-action 'none'",
        ]);
    }
}
