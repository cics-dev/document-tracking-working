<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\ExternalDocument;
use App\Services\DocumentQueryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        View::composer([
            'components.layouts.app',
            'components.layouts.app.sidebar',
        ], function ($view) {
            $user = Auth::user();

            $unreadExternal = 0;
            $unreadReceived = 0;
            $unreadAll = 0;

            if ($user) {
                $userOffice = $user->office;
                $userId = $user->id;
                $extQuery = ExternalDocument::query();

                $isPrivileged = $user->position == 'Staff'
                             || $user->position == 'University President'
                             || optional($user->office)->name == 'Records Section';

                if (! $isPrivileged) {
                    $extQuery->where('to_id', $user->office_id);
                }

                $unreadExternal = $extQuery->whereDoesntHave('accessLogs', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('action', 'Viewed');
                })->count();

                $logConstraint = fn ($q) => $q->where('user_id', $userId)->where('action', 'Viewed');

                $unreadReceived = app(DocumentQueryService::class)
                    ->receivedBy(Document::query(), $user)
                    ->whereDoesntHave('accessLogs', $logConstraint)
                    ->count();

                $unreadAll = Document::query()
                    ->whereDoesntHave('accessLogs', function ($q) use ($user) {
                        $q->where('user_id', $user->id)->where('action', 'Viewed');
                    })->count();
            }

            $view->with('unreadExternalCount', $unreadExternal);
            $view->with('unreadReceivedCount', $unreadReceived);
            $view->with('unreadAllCount', $unreadAll);
        });
    }
}
