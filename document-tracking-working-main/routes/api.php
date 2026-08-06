<?php

use App\Http\Controllers\OfficeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentTrackingController;

Route::apiResource('offices', OfficeController::class);

Route::get('/documents/{document}/tracking', [DocumentTrackingController::class, 'getTrackingStatus']);

Route::get('/documents/{document}/tracking-status', function(Document $document) {
    return response()->json([
        'status' => $document->status,
        'assignedTo' => $document->currentOffice->name ?? $document->office->name ?? 'Unknown',
        'statusDates' => [
            'filed' => $document->getStatusDate('filed'),
            'sent' => $document->getStatusDate('sent'),
            'processing' => $document->getStatusDate('processing'),
            'completed' => $document->getStatusDate('completed'),
        ],
        'timeline' => $document->buildTimelineData(),
        'activityLogs' => $document->getRecentLogs()
    ]);
})->name('documents.tracking-status');