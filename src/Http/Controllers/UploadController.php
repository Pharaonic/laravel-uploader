<?php

namespace Pharaonic\Laravel\Uploader\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Pharaonic\Laravel\Uploader\Facades\Uploader;
use Pharaonic\Laravel\Uploader\Support\Disposition;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UploadController
{
    /**
     * Stream the file.
     *
     * @return StreamedResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if no upload has the hash.
     * @throws NotFoundHttpException if the file is missing from the disk.
     */
    public function __invoke(string $hash)
    {
        if ((Uploader::options()['extensional'] ?? true) && str_contains($hash, '.')) {
            $hash = substr($hash, 0, (int) strrpos($hash, '.'));
        }

        $file = uploaded($hash);
        $disk = Storage::disk($file->disk);

        if (! $disk->exists($file->path)) {
            throw new NotFoundHttpException('File Not Found.');
        }

        return new StreamedResponse(
            function () use ($disk, $file) {
                $stream = $disk->readStream($file->path);

                if (! is_resource($stream)) {
                    return;
                }

                while (! feof($stream)) {
                    echo fread($stream, 8192);
                    flush();
                }

                fclose($stream);
            },
            200,
            [
                'Content-Type' => $file->mime,
                'Content-Disposition' => Disposition::make(Disposition::type($file->mime), $file->name),
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}
