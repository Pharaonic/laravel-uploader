<?php

namespace Pharaonic\Laravel\Uploader\Traits;

use Illuminate\Support\Facades\Storage;
use Pharaonic\Laravel\Uploader\Facades\Uploader;

trait Urls
{
    /**
     * Getting the file url.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return $this->url();
    }

    /**
     * Getting the file temporary url if exists.
     *
     * @return string
     */
    public function getTemporaryUrlAttribute()
    {
        return $this->temporaryUrl();
    }

    /**
     * Getting the file url.
     *
     * @return string
     */
    public function url()
    {
        if ($this->disk == 'local') {
            return route(
                'uploader.file',
                $this->hash . ((Uploader::options()['extensional'] ?? true) ? '.' . $this->extension : '')
            );
        }

        if ($this->visibility == 'private' && Storage::disk($this->disk)->providesTemporaryUrls()) {
            return $this->temporaryUrl();
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Getting the file temporary url if exists.
     *
     * @return string
     */
    public function temporaryUrl(int $expire = null)
    {
        $driver = Storage::disk($this->disk);

        if (!$driver->providesTemporaryUrls()) {
            throw new \Exception('The driver ' . $this->disk . ' does not support temporary URLs.');
        }

        return $driver->temporaryUrl(
            $this->path,
            now()->addMinutes($expire ?? Uploader::options()['expire']),
            [
                'ResponseContentDisposition' => 'filename="' . $this->name . '"'
            ]
        );
    }
}
