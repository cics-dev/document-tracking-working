<?php

use App\Http\Controllers\DocumentPreviewController;
use App\Http\Controllers\ChatBotController;
use App\Livewire\Documents\ReceiveExternalDocument;
use App\Livewire\Documents\ViewExternalDocument;
use App\Livewire\Documents\ListExternalDocuments;
use App\Livewire\Documents\CreateDocument;
use App\Livewire\Documents\ListDocuments;
use App\Livewire\Documents\TrackDocument;
use App\Livewire\Documents\ViewDocument;
use App\Livewire\Offices\CreateOffice;
use App\Livewire\Offices\ListOffices;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Users\CreateUser;
use App\Livewire\Users\ListUsers;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentTrackingController;


Route::get('/documents/{document}/tracking-status', [DocumentTrackingController::class, 'getTrackingStatus'])
    ->name('documents.tracking-status');


 Route::get('/offline', function () {
   return view('offline');
  })->name('offline');

Route::get('/help', function () {
    return view('help');
})->name('help');

// Route for the public landing page at "/"
Route::get('/landing', function () {
    return view('landing'); // shows landing.blade.php
})->name('landing');

Route::get('/', function () {
    return view('landing'); // shows landing.blade.php
})->name('landing');

Route::get('/learn', function () {
    return view('learn'); // shows landing.blade.php
})->name('learn');

// Route for the internal "home" page (after login), e.g., at "/home"
Route::get('/home', function () {
    return view('welcome'); // shows welcome.blade.php
})->middleware('auth')->name('home');

Route::view('dashboard', 'dashboard')
->middleware(['auth', 'verified'])
->name('dashboard');

Route::get('/document/preview', [DocumentPreviewController::class, 'preview']);
Route::post('/chat/send', [ChatBotController::class, 'sendChat'])->name('chat.send');

Route::middleware(['auth'])->group(function () {
    Route::prefix('offices')->name('offices.')->group(function () {
        Route::get('/', ListOffices::class)->name('list-offices');
        Route::get('/create', CreateOffice::class)->name('create-office');
        // Route::get('/edit', CreateOffice::class)->name('edit-office');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', ListUsers::class)->name('list-users');
        Route::get('/create', CreateUser::class)->name('create-user');
    });

    Route::prefix('documents')->name('documents.')->group(function () {
        // Route::get('/received', ListDocuments::class)->name('recieved-documents');
        // Route::get('/sent', ListDocuments::class)->name('sent-documents');
        Route::get('/view-external-document/{id}', ViewExternalDocument::class)->name('view-external-document');
        Route::get('/receive-external-document', ReceiveExternalDocument::class)->name('receive-external-document');
        Route::get('/list-external-documents', ListExternalDocuments::class)->name('list-external-documents');
        Route::get('/{mode}', ListDocuments::class)->whereIn('mode', ['sent', 'received', 'all'])->name('list-documents');
        Route::get('/track/{number}', TrackDocument::class)->name('track-document');
        Route::get('/create', CreateDocument::class)->name('create-document');
        Route::get('/create-revision/{number}', CreateDocument::class)->name('create-revision');
        Route::get('/view/{number}', ViewDocument::class)->name('view-document');
    });

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

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

require __DIR__.'/auth.php';
