<?php

return [
    /**
     * Storage Disk (local, public, s3, ...)
     */
    'disk'  => 'public',

    /**
     * Uploading Path
     */
    'path'  => '/',

    /**
     * Route URL /..../hash
     */
    'route' => 'file',

    /**
     * User Model
     */
    'user'  => \App\User::class,

    /**
     * Contoller Class for handling permissions
     * Allow custom controllers.
     */
    'controller'  => \Pharaonic\Laravel\Uploader\Controller\UploadController::class,

    /**
     * Expiry time of temporary-url in minutes
     */
    'expire' => 5,

    /**
     * Default Options
     */
    'options'   => [
        
        // Prefix Hash
        'prefix' => '',

        // Visits Counting
        'visitable' => false,

        // Permitting only specific users
        'private' => false,
    ]
];
