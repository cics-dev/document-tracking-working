<?php

namespace App\Http\Controllers;

use App\Models\Document;

class DocumentTrackingController extends Controller
{
    public function getTrackingStatus(Document $document)
    {
        try {
            $document->load(['logs', 'fromOffice', 'toOffice', 'documentType', 'steps.user.office']);

            // Get status dates from logs
            $statusDates = [
                'filed' => $this->getStatusDate($document, 'filed'),
                'Sent' => $this->getStatusDate($document, 'Sent'),
                'Processing' => $this->getStatusDate($document, 'Processing'),
                'Completed' => $this->getStatusDate($document, 'Completed'),
            ];

            // Build timeline data
            $timeline = $this->buildTimelineData($document);

            // Get recent activity logs
            $activityLogs = $document->logs()
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'action' => $log->action,
                        'description' => $log->description,
                        'created_at' => $log->created_at->format('F d, Y h:i A'),
                    ];
                });

            return response()->json([
                'status' => $document->status,
                'assignedTo' => $this->assignedTo($document),
                'subject' => $document->subject,
                'statusDates' => $statusDates,
                'timeline' => $timeline,
                'activityLogs' => $activityLogs,
                'last_updated' => $document->updated_at->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch tracking data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function getStatusDate($document, $status)
    {
        $log = $document->logs
            ->first(fn ($log) => strtolower($log->action) === $status);

        return $log ? $log->created_at->format('M d, h:i A') : '-';
    }

    private function buildTimelineData($document)
    {
        $timeline = [];

        // Document creation
        $timeline[] = [
            'date' => $document->created_at->format('M d, Y'),
            'title' => 'Document Created',
            'description' => 'Document drafted and prepared for submission',
        ];

        // Status logs
        foreach ($document->logs as $log) {
            if (! in_array(strtolower($log->action), ['sent', 'signed', 'reviewed', 'returned', 'rejected'], true)) {
                continue;
            }

            $title = 'Document '.ucfirst($log->action);
            $description = $log->description ?: $this->getStatusDescription($log->action, $document);

            $timeline[] = [
                'date' => $log->created_at->format('M d, h:i A'),
                'title' => $title,
                'description' => $description,
            ];
        }

        return $timeline;
    }

    private function getStatusDescription($status, $document)
    {
        $descriptions = [
            'filed' => 'Document officially filed in the system',
            'Sent' => 'Document forwarded to '.$this->assignedTo($document).' for review',
            'Processing' => 'Document is being reviewed and processed',
            'Completed' => 'Document processing has been completed',
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
