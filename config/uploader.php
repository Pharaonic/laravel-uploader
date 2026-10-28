<?php

return [
    /**
     * Storage disk (local, public, s3, ...).
     */
    'disk' => env('FILESYSTEM_DISK', env('FILESYSTEM_DRIVER', 'local')),

    /**
     * Base path of the uploaded files on the disk.
     */
    'path' => '/',

    /**
     * Expiry time of temporary URLs, in minutes (S3).
     */
    'expire' => 30,

    /**
     * Visibility of the uploaded files (S3).
     */
    'visibility' => 'private',

    /**
     * Include the file extension in the URL (local).
     */
    'extensional' => true,

    /**
     * Controller of the file route.
     */
    'controller' => \Pharaonic\Laravel\Uploader\Http\Controllers\UploadController::class,

    /**
     * URI of the file route (/file/{hash}).
     */
    'uri' => '/file',
];
