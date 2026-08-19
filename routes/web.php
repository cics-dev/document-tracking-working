<?php

use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentAttachmentPreviewController;
use App\Http\Controllers\DocumentPreviewController;
use App\Http\Controllers\DocumentTrackingController;
use App\Http\Controllers\ExternalDocumentPreviewController;
use App\Livewire\AccessRights\ManageAccessRights;
use App\Livewire\DocumentFlows\ManageDocumentFlows;
use App\Livewire\Documents\CreateDocument;
use App\Livewire\Documents\ListDocuments;
use App\Livewire\Documents\ListExternalDocuments;
use App\Livewire\Documents\ReceiveExternalDocument;
use App\Livewire\Documents\TrackDocument;
use App\Livewire\Documents\ViewDocument;
use App\Livewire\Documents\ViewExternalDocument;
use App\Livewire\DocumentTypes\ManageDocumentTypes;
use App\Livewire\Offices\CreateOffice;
use App\Livewire\Offices\ListOffices;
use App\Livewire\Roles\ManageRoles;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Office as OfficeSettings;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Users\CreateUser;
use App\Livewire\Users\ListUsers;
use Illuminate\Support\Facades\Route;

Route::view('/offline', 'offline')->name('offline');
Route::view('/help', 'help')->name('help');
Route::view('/landing', 'landing');
Route::view('/', 'landing')->name('landing');
Route::view('/learn', 'learn')->name('learn');
Route::view('/home', 'welcome')->middleware('auth')->name('home');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::post('/chat/send', [ChatBotController::class, 'sendChat'])->name('chat.send');

Route::middleware(['auth'])->group(function () {
    Route::get('/document/preview', [DocumentPreviewController::class, 'preview'])->name('document.preview');
    Route::get('/documents/{document}/tracking-status', [DocumentTrackingController::class, 'getTrackingStatus'])
        ->name('documents.tracking-status');

    Route::get('/access-rights', ManageAccessRights::class)->name('access-rights');
    Route::get('/roles', ManageRoles::class)->name('roles');
    Route::get('/document-flows', ManageDocumentFlows::class)->name('document-flows');
    Route::get('/document-types', ManageDocumentTypes::class)->name('document-types');
    Route::prefix('offices')->name('offices.')->group(function () {
        Route::get('/', ListOffices::class)->name('list-offices');
        Route::get('/create', CreateOffice::class)->name('create-office');
        Route::get('/edit/{id}', CreateOffice::class)->name('edit-office');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', ListUsers::class)->name('list-users');
        Route::get('/create', CreateUser::class)->name('create-user');
        Route::get('/edit/{id}', CreateUser::class)->name('edit-user');
    });

    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/external-document/{externalDocument}/preview', ExternalDocumentPreviewController::class)->name('external-document-preview');
        Route::get('/attachment/{documentAttachment}/preview', DocumentAttachmentPreviewController::class)->name('attachment-preview');
        Route::get('/view-external-document/{id}', ViewExternalDocument::class)->name('view-external-document');
        Route::get('/receive-external-document', ReceiveExternalDocument::class)->name('receive-external-document');
        Route::get('/list-external-documents', ListExternalDocuments::class)->name('list-external-documents');
        Route::get('/{mode}', ListDocuments::class)->whereIn('mode', ['Sent', 'received', 'all'])->name('list-documents');
        Route::get('/create', CreateDocument::class)->name('create-document');
        Route::get('/create-revision/{number}', CreateDocument::class)->name('create-revision');
        Route::get('/edit-draft/{draft_id}', CreateDocument::class)->name('edit-draft');
        Route::get('/view/{number}', ViewDocument::class)->name('view-document');
        Route::get('/track/{number}', TrackDocument::class)->name('track-document');
    });

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    Route::get('settings/office', OfficeSettings::class)->name('settings.office');
});

require __DIR__.'/auth.php';
