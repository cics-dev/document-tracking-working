<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
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

        // Let uploads on slow connections finish before the signed URL expires.
        config(['livewire.temporary_file_upload.max_upload_time' => 30]);
    }
}
