<?php

namespace Pharaonic\Laravel\Uploader\Actions;

use Illuminate\Support\Facades\Storage;
use Pharaonic\Laravel\Uploader\Models\Upload;

class DeleteFile
{
    /**
     * Delete file with the thumbnail.
     *
     * @param  Upload $file
     * @return void
     */
    public function handle(Upload $file)
    {
        if ($file->thumbnail_id) {
            $file->thumbnail->delete();
        }

        $fs = Storage::disk($file->disk);

        if (!$fs->exists($file->path)) {
            return;
        }

        return $fs->delete($file->path);
    }
}
