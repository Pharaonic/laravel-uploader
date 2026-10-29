<?php

namespace Pharaonic\Laravel\Uploader\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use Pharaonic\Laravel\Uploader\Facades\Uploader;
use Pharaonic\Laravel\Uploader\Models\Upload;
use RuntimeException;
use Throwable;

class CreateFile
{
    /**
     * Handle the action.
     *
     * @param  array<mixed>  $options
     * @return Upload
     *
     * @throws InvalidArgumentException if a thumbnail is requested without a width or height.
     */
    public function handle(UploadedFile $file, array $options = [])
    {
        $mime = (string) $file->getMimeType();
        $thumbnail = $this->wantsThumbnail($mime, $options) ? $this->thumbnailOptions($options) : null;

        $disk = $this->disk($options);
        $visibility = $this->visibility($disk, $options);
        $path = $this->path($options);

        $hash = $this->generateHash($file);
        $name = $hash.'.'.$file->extension();

        $file->storeAs($path, $name, array_filter([
            'disk' => $disk,
            'visibility' => $visibility,
        ]));

        if ($thumbnail !== null) {
            unset($options['thumbnail']);

            try {
                $thumbnail = $this->uploadThumbnail($file, $thumbnail, $options);
            } catch (Throwable $e) {
                Storage::disk($disk)->delete($path.$name);

                throw $e;
            }
        }

        $upload = new Upload([
            'thumbnail_id' => $thumbnail?->id,
            'hash' => $hash,
            'disk' => $disk,
            'visibility' => $visibility,
            'path' => $path.$name,
            'extension' => $file->extension(),
            'name' => $file->getClientOriginalName(),
            'mime' => $mime,
            'size' => $file->getSize(),
        ]);
        $upload->save();

        return $upload;
    }

    /**
     * Generate a new Hash.
     *
     * @return string
     */
    protected function generateHash(UploadedFile $file)
    {
        $hash = Str::random(27).pathinfo($file->hashName(), PATHINFO_FILENAME);

        if (Upload::query()->where('hash', $hash)->exists()) {
            return $this->generateHash($file);
        }

        return $hash;
    }

    /**
     * Get the disk name.
     *
     * @param  array<mixed>  $options
     */
    protected function disk(array $options): string
    {
        $disk = $options['disk'] ?? Uploader::options()['disk'] ?? null;

        return is_string($disk) ? $disk : 'local';
    }

    /**
     * Get the visibility, which only applies to non-local drivers.
     *
     * @param  array<mixed>  $options
     */
    protected function visibility(string $disk, array $options): ?string
    {
        if (Config::get('filesystems.disks.'.$disk.'.driver') === 'local') {
            return null;
        }

        $visibility = $options['visibility'] ?? Uploader::options()['visibility'] ?? null;

        return is_string($visibility) ? $visibility : null;
    }

    /**
     * Get the directory of the file on the disk, ending with a slash.
     *
     * @param  array<mixed>  $options
     */
    protected function path(array $options): string
    {
        $base = Uploader::options()['path'] ?? '';
        $path = trim(is_string($base) ? $base : '', '/').'/';

        $filePath = trim(is_string($options['path'] ?? null) ? $options['path'] : '', '/');

        if ($filePath !== '') {
            $path .= $filePath.'/';
        }

        return $path.date('Y-m-d').'/';
    }

    /**
     * Determine whether a thumbnail should be generated.
     *
     * @param  array<mixed>  $options
     */
    protected function wantsThumbnail(string $mime, array $options): bool
    {
        return isset($options['thumbnail'])
            && str_starts_with($mime, 'image/')
            && $mime !== 'image/svg+xml';
    }

    /**
     * Validate the thumbnail options.
     *
     * @param  array<mixed>  $options
     * @return array{ratio: bool, width: int|null, height: int|null}
     *
     * @throws InvalidArgumentException
     */
    protected function thumbnailOptions(array $options): array
    {
        $thumbnail = is_array($options['thumbnail'] ?? null) ? $options['thumbnail'] : [];

        $width = isset($thumbnail['width']) && is_numeric($thumbnail['width']) ? (int) $thumbnail['width'] : null;
        $height = isset($thumbnail['height']) && is_numeric($thumbnail['height']) ? (int) $thumbnail['height'] : null;

        if (! $width && ! $height) {
            throw new InvalidArgumentException('You have to set width or height thumbnail\'s option.');
        }

        return [
            'ratio' => (bool) ($thumbnail['ratio'] ?? false),
            'width' => $width ?: null,
            'height' => $height ?: null,
        ];
    }

    /**
     * Upload Thumbnail.
     *
     * @param  array{ratio: bool, width: int|null, height: int|null}  $thumbnail
     * @param  array<mixed>  $options
     * @return Upload
     */
    protected function uploadThumbnail(UploadedFile $file, array $thumbnail, array $options = [])
    {
        // Laravel 13 binds `image` to its own image manager, so the Intervention facade is not used.
        $config = Config::get('image');
        $image = (new ImageManager(is_array($config) ? $config : []))->make($file);

        if ($thumbnail['ratio']) {
            $image->resize(
                $thumbnail['width'],
                $thumbnail['width'] ? null : $thumbnail['height'],
                fn (Constraint $constraint) => $constraint->aspectRatio()
            );
        } else {
            $image->resize($thumbnail['width'], $thumbnail['height']);
        }

        $temporary = tempnam(sys_get_temp_dir(), 'pharaonic-thumb-');

        if ($temporary === false) {
            throw new RuntimeException('Unable to create a temporary thumbnail file.');
        }

        try {
            $image->save($temporary, 100, $file->extension());

            $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'-thumbnail.'.$file->extension();

            return $this->handle(new UploadedFile($temporary, $name, $image->mime()), $options);
        } finally {
            @unlink($temporary);
        }
    }
}
