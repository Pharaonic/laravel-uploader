<?php

use Illuminate\Http\UploadedFile;
use Pharaonic\Laravel\Uploader\Upload;

/**
 * Upload File
 *
 * @var Illuminate\Http\UploadedFile $file
 * @var Pharaonic\Laravel\Uploader\Upload|null $perv
 * @var array|null $options
 */
function upload(UploadedFile $file, array $options = [])
{
    return Upload::upload($file, $options);
}

/**
 * Get Upload File
 *
 * @var string $hash
 */
function getUploaded(string $hash)
{
    return Upload::where('hash', $hash)->first();
}
