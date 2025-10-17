<?php

use App\Http\Controllers\DocumentPreviewController;
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



Route::get('/help', function () {
    return view('help');
})->name('help');

// Route for the public landing page at "/"
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

Route::middleware(['auth'])->group(function () {
    Route::prefix('offices')->name('offices.')->group(function () {
        Route::get('/', ListOffices::class)->name('list-offices');
        Route::get('/create', CreateOffice::class)->name('create-office');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', ListUsers::class)->name('list-users');
        Route::get('/create', CreateUser::class)->name('create-user');
    });

    Route::prefix('documents')->name('documents.')->group(function () {
        // Route::get('/received', ListDocuments::class)->name('recieved-documents');
        // Route::get('/sent', ListDocuments::class)->name('sent-documents');
        Route::get('/{mode}', ListDocuments::class)->whereIn('mode', ['sent', 'received', 'all'])->name('list-documents');
        Route::get('/track/{number}', TrackDocument::class)->name('track-document');
        Route::get('/create', CreateDocument::class)->name('create-document');
        Route::get('/view/{number}', ViewDocument::class)->name('view-document');
    });

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
