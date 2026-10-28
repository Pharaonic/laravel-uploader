<?php

namespace Pharaonic\Laravel\Uploader\Actions;

use Illuminate\Support\Facades\Storage;
use Pharaonic\Laravel\Uploader\Models\Upload;

class DeleteFile
{
    /**
     * Delete file with the thumbnail.
     *
     * @return void
     */
    public function handle(Upload $file)
    {
        if ($file->thumbnail_id && $file->thumbnail) {
            $file->thumbnail->delete();
        }

        $fs = Storage::disk($file->disk);

        if ($fs->exists($file->path)) {
            $fs->delete($file->path);
        }
    }
}
