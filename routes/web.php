<?php

use App\Http\Controllers\DocumentPreviewController;
<<<<<<< HEAD
use App\Http\Controllers\ChatBotController;
use App\Livewire\Documents\ReceiveExternalDocument;
use App\Livewire\Documents\ViewExternalDocument;
use App\Livewire\Documents\ListExternalDocuments;
=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
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
<<<<<<< HEAD
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
});
=======


// Route for the public landing page at "/"
Route::get('/', function () {
    return view('landing'); // shows landing.blade.php
})->name('landing');
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045

Route::get('/learn', function () {
    return view('learn'); // shows landing.blade.php
})->name('learn');

// Route for the internal "home" page (after login), e.g., at "/home"
Route::get('/home', function () {
    return view('welcome'); // shows welcome.blade.php
})->middleware('auth')->name('home');

<<<<<<< HEAD
Route::get('dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/document/preview', [DocumentPreviewController::class, 'preview']);
Route::post('/chat/send', [ChatBotController::class, 'sendChat'])->name('chat.send');
=======
Route::view('dashboard', 'dashboard')
->middleware(['auth', 'verified'])
->name('dashboard');

Route::get('/document/preview', [DocumentPreviewController::class, 'preview']);
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045

Route::middleware(['auth'])->group(function () {
    Route::prefix('offices')->name('offices.')->group(function () {
        Route::get('/', ListOffices::class)->name('list-offices');
        Route::get('/create', CreateOffice::class)->name('create-office');
<<<<<<< HEAD
        Route::get('/edit/{id}', CreateOffice::class)->name('edit-office');
=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', ListUsers::class)->name('list-users');
        Route::get('/create', CreateUser::class)->name('create-user');
<<<<<<< HEAD
        Route::get('/edit/{id}', CreateUser::class)->name('edit-user');
=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
    });

    Route::prefix('documents')->name('documents.')->group(function () {
        // Route::get('/received', ListDocuments::class)->name('recieved-documents');
        // Route::get('/sent', ListDocuments::class)->name('sent-documents');
<<<<<<< HEAD
        Route::get('/view-external-document/{id}', ViewExternalDocument::class)->name('view-external-document');
        Route::get('/receive-external-document', ReceiveExternalDocument::class)->name('receive-external-document');
        Route::get('/list-external-documents', ListExternalDocuments::class)->name('list-external-documents');
        Route::get('/{mode}', ListDocuments::class)->whereIn('mode', ['sent', 'received', 'all'])->name('list-documents');
        Route::get('/create', CreateDocument::class)->name('create-document');
        Route::get('/create-revision/{number}', CreateDocument::class)->name('create-revision');
        Route::get('/edit-draft/{draft_id}', CreateDocument::class)->name('edit-draft');
        Route::get('/view/{number}', ViewDocument::class)->name('view-document');
        Route::get('/track/{number}', TrackDocument::class)->name('track-document');
=======
        Route::get('/{mode}', ListDocuments::class)->whereIn('mode', ['sent', 'received', 'all'])->name('list-documents');
        Route::get('/track/{number}', TrackDocument::class)->name('track-document');
        Route::get('/create', CreateDocument::class)->name('create-document');
        Route::get('/view/{number}', ViewDocument::class)->name('view-document');
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
    });

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

<<<<<<< HEAD
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

=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
require __DIR__.'/auth.php';
