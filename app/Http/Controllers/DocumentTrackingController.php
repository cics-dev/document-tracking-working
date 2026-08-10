<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentTrackingController extends Controller
{
    public function getTrackingStatus(Document $document)
    {
        try {
            // Load only relationships that exist on the current document model.
            $document->load(['logs', 'fromOffice', 'toOffice', 'documentType', 'routings.user', 'signatories.user']);
            
            // Get status dates from logs
            $statusDates = [
                'filed' => $this->getStatusDate($document, 'filed'),
                'sent' => $this->getStatusDate($document, 'sent'),
                'processing' => $this->getStatusDate($document, 'processing'),
                'completed' => $this->getStatusDate($document, 'completed'),
            ];
            
            // Build timeline data
            $timeline = $this->buildTimelineData($document);
            
            // Get recent activity logs
            $activityLogs = $document->logs()
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function($log) {
                    return [
                        'action' => $log->action,
                        'description' => $log->description,
                        'created_at' => $log->created_at->format('F d, Y h:i A')
                    ];
                });
            
            return response()->json([
                'status' => $document->status,
                'assignedTo' => $this->assignedTo($document),
                'subject' => $document->subject,
                'statusDates' => $statusDates,
                'timeline' => $timeline,
                'activityLogs' => $activityLogs,
                'last_updated' => $document->updated_at->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch tracking data',
                'message' => $e->getMessage()
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
            'description' => 'Document drafted and prepared for submission'
        ];
        
        // Status logs
        foreach ($document->logs as $log) {
            if (!in_array(strtolower($log->action), ['sent', 'signed', 'reviewed', 'returned'], true)) {
                continue;
            }

            $title = 'Document ' . ucfirst($log->action);
            $description = $log->description ?: $this->getStatusDescription($log->action, $document);

            $timeline[] = [
                'date' => $log->created_at->format('M d, h:i A'),
                'title' => $title,
                'description' => $description
            ];
        }
        
        return $timeline;
    }
    
    private function getStatusDescription($status, $document)
    {
        $descriptions = [
            'filed' => 'Document officially filed in the system',
            'sent' => 'Document forwarded to ' . $this->assignedTo($document) . ' for review',
            'processing' => 'Document is being reviewed and processed',
            'completed' => 'Document processing has been completed'
        ];
        
        return $descriptions[$status] ?? 'Document status updated';
    }

    private function assignedTo(Document $document): string
    {
        $pendingRouting = $document->routings
            ->first(fn ($routing) => is_null($routing->reviewed_at) && is_null($routing->returned_at));

        if ($pendingRouting?->user?->office?->name) {
            return $pendingRouting->user->office->name;
        }

        $pendingSignatory = $document->signatories
            ->sortBy('sequence')
            ->first(fn ($signatory) => is_null($signatory->signed_at) && is_null($signatory->rejected_at));

        return $pendingSignatory?->user?->office?->name
            ?? $document->toOffice?->name
            ?? 'Unknown';
    }
}
