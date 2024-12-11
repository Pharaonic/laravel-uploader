<?php

namespace Pharaonic\Laravel\Uploader\Observers;

use Illuminate\Support\Facades\Storage;
use Pharaonic\Laravel\Uploader\Models\Upload;

class UploadObserver
{
    /**
     * Handle the Upload "updated" event.
     *
     * @param  Upload  $upload
     * @return void
     */
    public function updated(Upload $upload)
    {
        if ($upload->wasChanged('visibility')) {
            Storage::disk($upload->disk)->setVisibility($upload->path, $upload->visibility);
        }
    }

    /**
     * Handle the Upload "deleting" event.
     *
     * @param  Upload  $file
     * @return void
     */
    public function deleting(Upload $file)
    {
        if ($file->thumbnail_id) {
            $file->thumbnail->delete();
        }

        // todo: delete action
    }
}
