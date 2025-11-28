<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\ExternalDocument; 
use App\Models\Document;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        View::composer([
            'components.layouts.app', 
            'components.layouts.app.sidebar' 
        ], function ($view) {
            $user = Auth::user();
            
            // Initialize default values
            $unreadExternal = 0;
            $unreadReceived = 0;
            $unreadAll = 0; // <--- NEW VARIABLE

            // if ($user) {
            //     // ... (Your existing External logic) ...
            //     $extQuery = ExternalDocument::query();
                
            //     $isPrivileged = $user->position == 'Staff' 
            //                  || $user->position == 'University President' 
            //                  || optional($user->office)->name == 'Records Section';

            //     if (!$isPrivileged) {
            //         $extQuery->where('to_id', $user->office_id);
            //     }

            //     $unreadExternal = $extQuery->whereDoesntHave('accessLogs', function ($q) use ($user) {
            //         $q->where('user_id', $user->id)->where('action', 'viewed');
            //     })->count();


            //     // ... (Your existing Received logic) ...
            //     $unreadReceived = Document::where('document_level', '!=', 'Intra')
            //         ->whereDoesntHave('accessLogs', function ($q) use ($user) {
            //             $q->where('user_id', $user->id)->where('action', 'viewed');
            //         })->count();

            //     // 3. All Documents Logic (New)
            //     // This counts ALL unread internal documents (Intra + Others)
            //     // Note: If users are only allowed to see specific offices, add that ->where() here too.
            //     $unreadAll = Document::query()
            //         ->whereDoesntHave('accessLogs', function ($q) use ($user) {
            //             $q->where('user_id', $user->id)->where('action', 'viewed');
            //         })->count();
            // }

            // Pass all variables to the view
            $view->with('unreadExternalCount', $unreadExternal);
            $view->with('unreadReceivedCount', $unreadReceived);
            $view->with('unreadAllCount', $unreadAll); // <--- PASS TO VIEW
        });
    }
}
