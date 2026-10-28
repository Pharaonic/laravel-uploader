<?php

namespace Pharaonic\Laravel\Uploader\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Pharaonic\Laravel\Readable\ReadableServiceProvider;
use Pharaonic\Laravel\Uploader\UploaderServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    /**
     * Temporary files created by the test.
     *
     * @var array<int, string>
     */
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            ImageServiceProvider::class,
            ReadableServiceProvider::class,
            UploaderServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }

    /**
     * Create a real file on disk and return an UploadedFile for it.
     */
    protected function realFile(string $name, string $content): UploadedFile
    {
        return new UploadedFile($this->temporaryFile($name, $content), $name, null, null, true);
    }

    /**
     * Create a temporary file with the given name and content.
     */
    protected function temporaryFile(string $name, string $content): string
    {
        $directory = sys_get_temp_dir().'/pharaonic-uploader-tests/'.bin2hex(random_bytes(6));
        @mkdir($directory, 0777, true);

        $path = $directory.'/'.$name;
        file_put_contents($path, $content);

        return $this->temporaryFiles[] = $path;
    }

    protected function requiresGd(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('The gd extension is not loaded.');
        }
    }
}
