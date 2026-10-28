<?php

namespace Pharaonic\Laravel\Uploader\Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Pharaonic\Laravel\Uploader\Http\Controllers\UploadController;
use Pharaonic\Laravel\Uploader\Models\Upload;
use Pharaonic\Laravel\Uploader\Tests\TestCase;
use Pharaonic\Laravel\Uploader\UploaderServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function test_it_registers_the_service_provider(): void
    {
        $this->assertNotEmpty($this->app->getProviders(UploaderServiceProvider::class));
    }

    public function test_it_merges_the_configuration_under_the_canonical_key(): void
    {
        $this->assertSame([
            'disk' => 'local',
            'path' => '/',
            'expire' => 30,
            'visibility' => 'private',
            'extensional' => true,
            'controller' => UploadController::class,
            'uri' => '/file',
        ], config('pharaonic.uploader'));
    }

    public function test_it_keeps_the_deprecated_key_in_sync(): void
    {
        $this->assertSame(config('pharaonic.uploader'), config('Pharaonic.uploader'));
    }

    public function test_it_publishes_the_config_and_migrations_with_four_tags_each(): void
    {
        $config = [realpath(__DIR__.'/../../config/uploader.php') => $this->app->configPath('pharaonic/uploader.php')];
        $migrations = [realpath(__DIR__.'/../../database/migrations') => $this->app->databasePath('migrations')];

        foreach (['pharaonic-config', 'uploader-config'] as $tag) {
            $this->assertSame($config, $this->normalize(ServiceProvider::pathsToPublish(UploaderServiceProvider::class, $tag)));
        }

        foreach (['pharaonic-migrations', 'uploader-migrations'] as $tag) {
            $this->assertSame($migrations, $this->normalize(ServiceProvider::pathsToPublish(UploaderServiceProvider::class, $tag)));
        }

        foreach (['pharaonic', 'laravel-uploader'] as $tag) {
            $this->assertSame($config + $migrations, $this->normalize(ServiceProvider::pathsToPublish(UploaderServiceProvider::class, $tag)));
        }
    }

    public function test_it_registers_the_file_route_without_middleware(): void
    {
        $route = Route::getRoutes()->getByName('uploader.file');

        $this->assertNotNull($route);
        $this->assertSame('file/{hash}', $route->uri());
        $this->assertSame(['GET', 'HEAD'], $route->methods());
        $this->assertSame([], $route->gatherMiddleware());
        $this->assertSame(UploadController::class, $route->getActionName());
    }

    public function test_it_loads_the_migrations(): void
    {
        $this->assertTrue(Schema::hasTable('uploads'));
        $this->assertTrue(Schema::hasColumns('uploads', [
            'id', 'thumbnail_id', 'hash', 'disk', 'visibility', 'name', 'path', 'size', 'extension', 'mime', 'created_at', 'updated_at',
        ]));
    }

    public function test_the_hash_is_unique(): void
    {
        $attributes = ['hash' => 'same', 'name' => 'a.txt', 'path' => 'a.txt', 'size' => 1, 'extension' => 'txt', 'mime' => 'text/plain'];
        (new Upload($attributes))->save();

        $this->expectException(QueryException::class);

        (new Upload($attributes))->save();
    }

    public function test_the_unique_hash_migration_can_be_rolled_back(): void
    {
        $this->artisan('migrate:rollback', ['--step' => 1])->run();

        $attributes = ['hash' => 'same', 'name' => 'a.txt', 'path' => 'a.txt', 'size' => 1, 'extension' => 'txt', 'mime' => 'text/plain'];
        (new Upload($attributes))->save();
        (new Upload($attributes))->save();

        $this->assertSame(2, Upload::query()->where('hash', 'same')->count());
    }

    /**
     * @param  array<string, string>  $paths
     * @return array<string, string>
     */
    private function normalize(array $paths): array
    {
        $normalized = [];

        foreach ($paths as $from => $to) {
            $normalized[(string) realpath($from)] = $to;
        }

        return $normalized;
    }
}
