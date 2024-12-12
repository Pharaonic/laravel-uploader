<?php

namespace Pharaonic\Laravel\Uploader\Observers;

use Illuminate\Support\Facades\Storage;
use Pharaonic\Laravel\Uploader\Actions\DeleteFile;
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
     * @param  Upload  $upload
     * @return void
     */
    public function deleting(Upload $upload)
    {
        (new DeleteFile)->handle($upload);
    }
}
