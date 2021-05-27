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
            __DIR__ . '/config/uploader.php'                        => config_path('Pharaonic/uploader.php'),
            __DIR__ . '/database/migrations/uploads.stub'           => database_path(sprintf('migrations/%s_create_uploads_table.php',          date('Y_m_d_His', time() + 1))),
            __DIR__ . '/database/migrations/upload_permits.stub'    => database_path(sprintf('migrations/%s_create_upload_permits_table.php',   date('Y_m_d_His', time() + 2))),
        ], ['pharaonic', 'laravel-uploader']);

        // Routes
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }
}
