<?php

namespace Pharaonic\Laravel\Uploader\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array options()
 * @method static Router route()
 * @method static \Pharaonic\Laravel\Uploader\Models\Upload upload(\Illuminate\Http\UploadedFile|string $file, array $options = [])
 */
class Uploader extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pharaonic.uploader';
    }
}
