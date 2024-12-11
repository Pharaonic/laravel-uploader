<?php

namespace Pharaonic\Laravel\Uploader\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array options() Get uploader options.
 * @method static Router route() Create a new Router instance.
 * @method static \Pharaonic\Laravel\Uploader\Models\Upload upload(\Illuminate\Http\UploadedFile|string $file, array $options = []) Upload a new file.
 */
class Uploader extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pharaonic.uploader';
    }
}
