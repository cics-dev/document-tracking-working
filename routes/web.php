<?php

use App\Livewire\Documents\CreateDocument;
use App\Livewire\Documents\ListDocuments;
use App\Livewire\Offices\CreateOffice;
use App\Livewire\Offices\ListOffices;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Users\CreateUser;
use App\Livewire\Users\ListUsers;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
->middleware(['auth', 'verified'])
->name('dashboard');

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
        Route::get('/{mode}', ListDocuments::class)->whereIn('mode', ['sent', 'received'])->name('list-documents');
        Route::get('/create', CreateDocument::class)->name('create-document');
    });

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
