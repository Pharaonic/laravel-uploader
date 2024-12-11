<?php

namespace Pharaonic\Laravel\Uploader;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Pharaonic\Laravel\Uploader\Models\Upload;
use Pharaonic\Laravel\Uploader\Observers\UploadObserver;

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
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Config Merge
        $this->mergeConfigFrom(
            __DIR__ . '/../config/uploader.php',
            'Pharaonic.uploader'
        );

        // Create Uploader Instance
        $this->app->singleton('pharaonic.uploader', fn() => new Classes\Uploader);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //  Information
        AboutCommand::add('Pharaonic', fn () => ['Uploader' => '4.0.0']);

        // Observers
        Upload::observe(UploadObserver::class);

        // Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Publishes
        $this->publishes(
            [
                __DIR__ . '/../config'  => config_path('Pharaonic'),
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ],
            ['pharaonic', 'laravel-uploader']
        );
    }
}
