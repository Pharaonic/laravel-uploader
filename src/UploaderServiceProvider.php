<?php

namespace Pharaonic\Laravel\Uploader;

use Illuminate\Support\ServiceProvider;

class UploaderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Config Merge
        $this->mergeConfigFrom(__DIR__ . '/config/uploader.php', 'Pharaonic.uploader');

        // Setting Default User Model
        if (app()->version() > 7 && config('Pharaonic.uploader.user') == 'App\User')
            config(['Pharaonic.uploader.user' => 'App\Models\User']);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publishes
        $this->publishes([
            __DIR__ . '/config/uploader.php'                                            => config_path('Pharaonic/uploader.php'),
            __DIR__ . '/database/migrations/2021_02_01_000001_create_uploads_table.php' => database_path('migrations/2021_02_01_000001_create_uploads_table.php'),
            __DIR__ . '/database/migrations/2021_02_01_000002_upload_permits_table.php' => database_path('migrations/2021_02_01_000002_upload_permits_table.php'),
        ], ['pharaonic', 'laravel-uploader']);

        // Routes
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }
}
