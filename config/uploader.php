<?php

return [
    /**
     * Storage Disk (local, public, s3, ...)
     */
    'disk'  => env('FILESYSTEM_DISK', 'local'),

    /**
     * Uploading Path
     */
    'path'  => '/',

    /**
     * Expiry time of temporary-url in minutes (S3)
     */
    'expire' => 30,

    /**
     * Visibility of the uploaded files (S3)
     */
    'visibility' => 'private',

    /**
     * Include the file extension in the URL (local)
     */
    'extensional' => true,

    /**
     * Route Contoller Class
     */
    'controller'  => \Pharaonic\Laravel\Uploader\Http\Controllers\UploadController::class,

    /**
     * Route URI (/file/{hash})
     */
    'uri' => '/file',
];
