<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentTrackingController extends Controller
{
    public function getTrackingStatus(Document $document)
    {
        try {
            // Eager load relationships to avoid N+1 queries
            $document->load(['status_logs', 'logs', 'currentOffice', 'office', 'documentType']);
            
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
                'assignedTo' => $document->currentOffice->name ?? $document->office->name ?? $document->assigned_to ?? $document->current_office ?? 'Unknown',
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
        $log = $document->status_logs->where('status', $status)->first();
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
        if ($document->status_logs) {
            foreach ($document->status_logs as $log) {
                $title = 'Document ' . ucfirst($log->status);
                $description = $this->getStatusDescription($log->status, $document);
                
                $timeline[] = [
                    'date' => $log->created_at->format('M d, h:i A'),
                    'title' => $title,
                    'description' => $description
                ];
            }
        }
        
        return $timeline;
    }
    
    private function getStatusDescription($status, $document)
    {
        $descriptions = [
            'filed' => 'Document officially filed in the system',
            'sent' => 'Document forwarded to ' . ($document->currentOffice->name ?? $document->office->name ?? 'relevant office') . ' for review',
            'processing' => 'Document is being reviewed and processed',
            'completed' => 'Document processing has been completed'
        ];
        
        return $descriptions[$status] ?? 'Document status updated';
    }
}