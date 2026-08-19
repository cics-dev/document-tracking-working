<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentLog;
use App\Services\DocumentQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DocumentTrackingController extends Controller
{
    public function getTrackingStatus(Document $document, DocumentQueryService $documents): JsonResponse
    {
        abort_unless($documents->canView(Auth::user(), $document->document_number), 403);

        $document->load(['logs', 'fromOffice', 'toOffice', 'documentType', 'steps.user.office']);

        return response()->json([
            'status' => $document->status,
            'assignedTo' => $this->assignedTo($document),
            'subject' => $document->subject,
            'statusDates' => collect([
                'filed' => 'filed',
                'Sent' => 'sent',
                'Processing' => 'processing',
                'Completed' => 'completed',
            ])->map(fn (string $status) => $this->statusDate($document, $status)),
            'timeline' => $this->timeline($document),
            'activityLogs' => $document->logs
                ->sortByDesc('created_at')
                ->take(10)
                ->map(fn (DocumentLog $log) => [
                    'action' => $log->action,
                    'description' => $log->description,
                    'created_at' => $log->created_at->format('F d, Y h:i A'),
                ])->values(),
            'last_updated' => $document->updated_at->toISOString(),
        ]);
    }

    private function statusDate(Document $document, string $status): string
    {
        $log = $document->logs
            ->first(fn (DocumentLog $log) => strtolower($log->action) === $status);

        return $log?->created_at->format('M d, h:i A') ?? '-';
    }

    private function timeline(Document $document): array
    {
        $timeline = [[
            'date' => $document->created_at->format('M d, Y'),
            'title' => 'Document Created',
            'description' => 'Document drafted and prepared for submission',
        ]];

        foreach ($document->logs as $log) {
            $action = strtolower($log->action);

            if (! in_array($action, ['sent', 'signed', 'reviewed', 'returned', 'rejected'], true)) {
                continue;
            }

            $timeline[] = [
                'date' => $log->created_at->format('M d, h:i A'),
                'title' => 'Document '.ucfirst($action),
                'description' => $log->description ?: $this->statusDescription($action, $document),
            ];
        }

        return $timeline;
    }

    private function statusDescription(string $status, Document $document): string
    {
        $descriptions = [
            'filed' => 'Document officially filed in the system',
            'sent' => 'Document forwarded to '.$this->assignedTo($document).' for review',
            'processing' => 'Document is being reviewed and processed',
            'completed' => 'Document processing has been completed',
        ];

        return $descriptions[$status] ?? 'Document status updated';
    }

    private function assignedTo(Document $document): string
    {
        $pendingStep = $document->steps
            ->sortBy(['sequence', 'id'])
            ->first(fn ($step) => $step->status === 'Pending');

        return $pendingStep?->active_user?->office?->name
            ?? $document->toOffice?->name
            ?? 'Unknown';
    }
}
