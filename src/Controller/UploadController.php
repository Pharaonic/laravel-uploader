<?php

namespace Pharaonic\Laravel\Uploader\Controller;

use App\Http\Controllers\Controller;
use Pharaonic\Laravel\Uploader\File;
use Pharaonic\Laravel\Uploader\Upload;
use Pharaonic\Laravel\Uploader\UploadPermit;

/**
 * Uploaded File Controller
 *
 * @version 2.0
 * @author Moamen Eltouny (Raggi) <raggi@raggitech.com>
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

            $isPermitted = UploadPermit::where('user_id', auth()->user()->id)->where('upload_id', $file->id)->first();
            if (!$isPermitted) return abort(404);
            if ($isPermitted->isExpired()) return abort(404);
        }

        // Stream the File
        return (new File($file))->start();
    }
}
