<?php

namespace Pharaonic\Laravel\Uploader;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
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
        $this->mergeLegacyConfig();

        $this->mergeConfigFrom(__DIR__.'/../config/uploader.php', 'pharaonic.uploader');

        // Deprecated 4.x key, kept in sync for code that still reads it.
        Config::set('Pharaonic.uploader', Config::get('pharaonic.uploader'));

        $this->app->singleton('pharaonic.uploader', fn () => new Classes\Uploader);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (class_exists(AboutCommand::class)) {
            AboutCommand::add('Pharaonic', fn () => [
                'Uploader' => InstalledVersions::getPrettyVersion('pharaonic/laravel-uploader') ?? 'unknown',
            ]);
        }

        Upload::observe(UploadObserver::class);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/uploader.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/uploader.php' => $this->app->configPath('pharaonic/uploader.php'),
            ], ['pharaonic', 'laravel-uploader', 'pharaonic-config', 'uploader-config']);

            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], ['pharaonic', 'laravel-uploader', 'pharaonic-migrations', 'uploader-migrations']);
        }
    }

    /**
     * Apply a config file published by 4.x (config/Pharaonic/uploader.php) under the
     * canonical key. A file published to config/pharaonic/uploader.php takes precedence.
     */
    private function mergeLegacyConfig(): void
    {
        $legacy = Config::get('Pharaonic.uploader');

        if (! is_array($legacy)) {
            return;
        }

        $current = Config::get('pharaonic.uploader');

        Config::set('pharaonic.uploader', array_merge($legacy, is_array($current) ? $current : []));
    }
}
