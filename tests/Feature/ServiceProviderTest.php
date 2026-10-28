<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Pharaonic\Laravel\Uploader\Http\Controllers\UploadController;
use Pharaonic\Laravel\Uploader\Models\Upload;
use Pharaonic\Laravel\Uploader\UploaderServiceProvider;

/**
 * @param  array<string, string>  $paths
 * @return array<string, string>
 */
function normalizePublishPaths(array $paths): array
{
    $normalized = [];

    foreach ($paths as $from => $to) {
        $normalized[(string) realpath($from)] = $to;
    }

    return $normalized;
}

test('it registers the service provider', function () {
    expect($this->app->getProviders(UploaderServiceProvider::class))->not->toBeEmpty();
});

test('it merges the configuration under the canonical key', function () {
    expect(config('pharaonic.uploader'))->toBe([
        'disk' => 'local',
        'path' => '/',
        'expire' => 30,
        'visibility' => 'private',
        'extensional' => true,
        'controller' => UploadController::class,
        'uri' => '/file',
    ]);
});

test('it keeps the deprecated key in sync', function () {
    expect(config('Pharaonic.uploader'))->toBe(config('pharaonic.uploader'));
});

test('it publishes the config and migrations with four tags each', function () {
    $config = [realpath(__DIR__.'/../../config/uploader.php') => $this->app->configPath('pharaonic/uploader.php')];
    $migrations = [realpath(__DIR__.'/../../database/migrations') => $this->app->databasePath('migrations')];

    foreach (['pharaonic-config', 'uploader-config'] as $tag) {
        expect(normalizePublishPaths(ServiceProvider::pathsToPublish(UploaderServiceProvider::class, $tag)))->toBe($config);
    }

    foreach (['pharaonic-migrations', 'uploader-migrations'] as $tag) {
        expect(normalizePublishPaths(ServiceProvider::pathsToPublish(UploaderServiceProvider::class, $tag)))->toBe($migrations);
    }

    foreach (['pharaonic', 'laravel-uploader'] as $tag) {
        expect(normalizePublishPaths(ServiceProvider::pathsToPublish(UploaderServiceProvider::class, $tag)))->toBe($config + $migrations);
    }
});

test('it registers the file route without middleware', function () {
    $route = Route::getRoutes()->getByName('uploader.file');

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('file/{hash}')
        ->and($route->methods())->toBe(['GET', 'HEAD'])
        ->and($route->gatherMiddleware())->toBe([])
        ->and($route->getActionName())->toBe(UploadController::class);
});

test('it loads the migrations', function () {
    expect(Schema::hasTable('uploads'))->toBeTrue()
        ->and(Schema::hasColumns('uploads', [
            'id', 'thumbnail_id', 'hash', 'disk', 'visibility', 'name', 'path', 'size', 'extension', 'mime', 'created_at', 'updated_at',
        ]))->toBeTrue();
});

test('the hash is unique', function () {
    $attributes = ['hash' => 'same', 'name' => 'a.txt', 'path' => 'a.txt', 'size' => 1, 'extension' => 'txt', 'mime' => 'text/plain'];
    (new Upload($attributes))->save();

    (new Upload($attributes))->save();
})->throws(QueryException::class);

test('the unique hash migration can be rolled back', function () {
    $this->artisan('migrate:rollback', ['--step' => 1])->run();

    $attributes = ['hash' => 'same', 'name' => 'a.txt', 'path' => 'a.txt', 'size' => 1, 'extension' => 'txt', 'mime' => 'text/plain'];
    (new Upload($attributes))->save();
    (new Upload($attributes))->save();

    expect(Upload::query()->where('hash', 'same')->count())->toBe(2);
});
