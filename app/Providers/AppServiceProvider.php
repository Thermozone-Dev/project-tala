<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

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
        Model::preventAccessingMissingAttributes();

        // Set Livewire temporary file upload size limit to 50MB (51200 KB)
        Config::set('livewire.temporary_file_upload.rules', [
            'required',
            'file',
            'max:51200', // 50MB in KB
        ]);
    }
}
