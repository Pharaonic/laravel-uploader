<?php

namespace Pharaonic\Laravel\Uploader\Concerns;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Pharaonic\Laravel\Uploader\Facades\Uploader;
use Pharaonic\Laravel\Uploader\Support\Disposition;
use RuntimeException;

trait HasUrls
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
            $extensional = Uploader::options()['extensional'] ?? true;

            return URL::route('uploader.file', $this->hash.($extensional ? '.'.$this->extension : ''));
        }

        if ($this->getAttributeValue('visibility') == 'private' && $this->providesTemporaryUrls()) {
            return $this->temporaryUrl();
        }

        return $this->filesystem()->url($this->path);
    }

    /**
     * Getting the file temporary url if exists.
     *
     * @return string
     *
     * @throws RuntimeException if the disk does not support temporary URLs.
     */
    public function temporaryUrl(?int $expire = null)
    {
        if (! $this->providesTemporaryUrls()) {
            throw new RuntimeException('The driver '.$this->disk.' does not support temporary URLs.');
        }

        $minutes = $expire ?? Uploader::options()['expire'] ?? 30;

        return $this->filesystem()->temporaryUrl(
            $this->path,
            Date::now()->addMinutes(is_numeric($minutes) ? (int) $minutes : 30),
            ['ResponseContentDisposition' => Disposition::make('inline', $this->name)]
        );
    }

    /**
     * Get the filesystem of the file disk.
     */
    protected function filesystem(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $filesystem */
        $filesystem = Storage::disk($this->disk);

        return $filesystem;
    }

    /**
     * Determine whether the file disk can create temporary URLs.
     */
    protected function providesTemporaryUrls(): bool
    {
        $disk = Storage::disk($this->disk);

        // Laravel 9.x and later.
        if (method_exists($disk, 'providesTemporaryUrls')) {
            return (bool) $disk->providesTemporaryUrls();
        }

        return Config::get('filesystems.disks.'.$this->disk.'.driver') === 's3';
    }
}
