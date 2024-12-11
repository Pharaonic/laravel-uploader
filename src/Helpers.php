<?php

use Illuminate\Http\UploadedFile;
use Pharaonic\Laravel\Uploader\Facades\Uploader;
use Pharaonic\Laravel\Uploader\Models\Upload;

if (!function_exists('upload')) {
    /**
     * Upload a new file.
     *
     * @param UploadedFile|string $file
     * @param array $options
     * 
     * @return Pharaonic\Laravel\Uploader\Upload
     * @throws Exception if the file path does not exist.
     */
    function upload(UploadedFile|string $file, array $options = [])
    {
        return Uploader::upload($file, $options);
    }
}

if (!function_exists('uploaded')) {
    /**
     * Get the uploaded file.
     *
     * @param string $hash
     * 
     * @return Pharaonic\Laravel\Uploader\Upload
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     */
    function uploaded(string $hash)
    {
        return Upload::whereHash($hash)->firstOrFail();
    }
}
