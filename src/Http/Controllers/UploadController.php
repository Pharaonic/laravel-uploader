<?php

namespace Pharaonic\Laravel\Uploader\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Pharaonic\Laravel\Uploader\Facades\Uploader;

class UploadController
{
    /**
     * Stream the file.
     *
     * @param string $hash
     * @return \Illuminate\Http\Response
     */
    public function __invoke(string $hash)
    {
        if (Uploader::options()['extensional'] && str_contains($hash, '.')) {
            $hash = explode('.', $hash);
            unset($hash[count($hash) - 1]);

            $hash = implode('.', $hash);
        }

        abort_if(
            !($file = uploaded($hash)),
            404,
            'File Not Found.'
        );

        return response()->stream(
            function () use ($file) {
                if ($stream = Storage::disk($file->disk)->readStream($file->path)) {
                    while (!feof($stream)) {
                        echo fread($stream, 1024);
                        flush();
                    }
                    fclose($stream);
                }

                return;
            },
            200,
            [
                'Content-Type' => $file->mime
            ]
        );
    }
}
