<?php

namespace Pharaonic\Laravel\Uploader\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<mixed> options() Get uploader options.
 * @method static \Pharaonic\Laravel\Uploader\Classes\Router route() Create a new Router instance.
 * @method static \Pharaonic\Laravel\Uploader\Models\Upload upload(\Illuminate\Http\UploadedFile|string $file, array<mixed> $options = []) Upload a new file.
 *
 * @see \Pharaonic\Laravel\Uploader\Classes\Uploader
 */
class Uploader extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pharaonic.uploader';
    }
}
