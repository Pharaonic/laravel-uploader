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
