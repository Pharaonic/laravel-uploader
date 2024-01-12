<?php

namespace Pharaonic\Laravel\Uploader\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Pharaonic\Laravel\Uploader\Upload;
use Pharaonic\Laravel\Uploader\UploadPermit;

/**
 * Uploaded File Controller
 *
 * @version 3.0
 * @author Moamen Eltouny (Raggi)
 */
class UploadController extends Controller
{
    public function file(string $hash)
    {
        $file = Upload::where('hash', $hash)->firstOrFail();

        // Visitable & Visits
        if ($file->visitable) $file->increment('visits');

        // Private
        if ($file->private) {
            if (!auth()->check()) return abort(404);

            // If the viewer isn't the owner or uploader, check if the user has permission to view the file.
            if (auth()->user()->id != $file->uploader_id) {
                $isPermitted = UploadPermit::where('user_id', auth()->user()->id)->where('upload_id', $file->id)->first();
                if (!$isPermitted) return abort(404);
                if ($isPermitted->isExpired()) return abort(404);
            }
        }

        return response()->stream(function () use ($file) {
            if ($stream = Storage::disk($file->disk)->readStream($file->path)) {
                while (!feof($stream)) {
                    echo fread($stream, 1024);
                    flush();
                }
                fclose($stream);
            }

            return;
        }, 200, [
            'Content-Type' => $file->mime
        ]);
    }
}
