<?php

use Illuminate\Http\UploadedFile;

if (!function_exists('upload')) {
    /**
     * Upload a file.
     *
     * @param UploadedFile|string $file
     * @param array $options
     * @return Pharaonic\Laravel\Uploader\Upload
     */
    function upload(UploadedFile|string $file, array $options = [])
    {
        // 
    }
}
