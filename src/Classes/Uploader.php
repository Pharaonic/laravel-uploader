<?php

namespace Pharaonic\Laravel\Uploader\Classes;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Pharaonic\Laravel\Uploader\Actions\CreateFile;
use Pharaonic\Laravel\Uploader\Models\Upload;

class Uploader
{
    /**
     * The options list.
     *
     * @var array<mixed>
     */
    protected array $options;

    /**
     * Create a new Uploader instance.
     */
    public function __construct()
    {
        $options = Config::get('pharaonic.uploader');

        $this->options = is_array($options) ? $options : [];
    }

    /**
     * Get uploader options.
     *
     * @return array<mixed>
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
     * @param  array<mixed>  $options
     * @return Upload
     *
     * @throws InvalidArgumentException if the file path does not exist.
     */
    public function upload(UploadedFile|string $file, array $options = [])
    {
        if (is_string($file)) {
            if (! is_file($file)) {
                throw new InvalidArgumentException('The file path does not exist.');
            }

            $file = new UploadedFile($file, basename($file));
        }

        return (new CreateFile)->handle($file, $options);
    }
}
