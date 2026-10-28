<?php

use Illuminate\Http\UploadedFile;
use Pharaonic\Laravel\Uploader\Facades\Uploader;
use Pharaonic\Laravel\Uploader\Models\Upload;

if (! function_exists('upload')) {
    /**
     * Upload a new file.
     *
     * @param  array<mixed>  $options
     */
    function upload(UploadedFile|string $file, array $options = []): Upload
    {
        return Uploader::upload($file, $options);
    }
}

if (! function_exists('uploaded')) {
    /**
     * Get the uploaded file.
     *
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     */
    function uploaded(string $hash): Upload
    {
        /** @var Upload $upload */
        $upload = Upload::query()->where('hash', $hash)->firstOrFail();

        return $upload;
    }
}
