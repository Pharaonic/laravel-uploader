<?php

namespace Pharaonic\Laravel\Uploader\Classes;

use Illuminate\Http\UploadedFile;
use Pharaonic\Laravel\Uploader\Actions\CreateFile;

/**
 * @method array options() Get uploader options.
 * @method Router route() Create a new Router instance.
 * @method \Pharaonic\Laravel\Uploader\Models\Upload upload(\Illuminate\Http\UploadedFile|string $file, array $options = []) Upload a new file.
 */
class Uploader
{
    /**
     * The options list.
     *
     * @var array
     */
    protected array $options;

    /**
     * Create a new Uploader instance.
     */
    public function __construct()
    {
        $this->options = config('Pharaonic.uploader');
    }

    /**
     * Get uploader options.
     *
     * @return array
     */
    public function options()
    {
        return $this->options;
    }

    /**
     * Create a new Router instance.
     *
     * @return Router
     */
    public function route()
    {
        return new Router($this->options);
    }

    /**
     * Upload a new file.
     *
     * @param UploadedFile|string $file
     * @param array|null $options
     * @return \Pharaonic\Laravel\Uploader\Models\Upload
     * 
     * @throws \Exception if the file path does not exist.
     */
    public function upload(UploadedFile|string $file, array $options = [])
    {
        if (is_string($file)) {
            if (!file_exists($file)) {
                throw new \Exception('The file path does not exist.');
            }

            $name = basename($file);
            $file = new UploadedFile(
                $file,
                pathinfo($name, PATHINFO_FILENAME),
                pathinfo($name, PATHINFO_EXTENSION),
            );
        }

        return (new CreateFile)->handle($file, $options);
    }
}
