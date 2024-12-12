<?php

namespace Pharaonic\Laravel\Uploader\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Pharaonic\Laravel\Uploader\Facades\Uploader;
use Pharaonic\Laravel\Uploader\Models\Upload;

class CreateFile
{
    /**
     * Handle the action.
     *
     * @param UploadedFile $file
     * @param array|null $options
     * @return Upload
     */
    public function handle(UploadedFile $file, array $options = [])
    {
        $hash = $this->generateHash($file);
        $extension = $file->extension();
        extract($this->options($options));

        $file->storeAs($path, $hash . '.' . $extension, [
            'disk' => $disk,
            'visibility' => $visibility,
        ]);

        // Thumbnail Generating
        if (str_starts_with($file->getMimeType(), 'image/') && isset($options['thumbnail'])) {
            $thumbnail = $this->uploadThumbnail($file, $options);
        }

        return Upload::create([
            'thumbnail_id'  => isset($thumbnail) ? $thumbnail->id : null,
            'hash'          => $hash,
            'disk'          => $disk,
            'visibility'    => $visibility,
            'path'          => $path . $hash . '.' . $extension,
            'extension'     => $extension,
            'name'          => $file->getClientOriginalName(),
            'mime'          => $file->getMimeType(),
            'size'          => $file->getSize(),
        ]);
    }

    /**
     * Generate a new Hash.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateHash(UploadedFile $file)
    {
        $hash = Str::random(27)  . pathinfo($file->hashName(), PATHINFO_FILENAME);

        if (Upload::whereHash($hash)->exists())
            return $this->generateHash($file);

        return $hash;
    }

    /**
     * Get the options list.
     *
     * @param array $options
     * @return array
     */
    protected function options(array $options)
    {
        $originalOptions = Uploader::options();

        $path = trim($originalOptions['path'], '/') . '/';
        $filePath = trim($options['path'] ?? '', '/');

        if (!empty($filePath)) {
            $path .= $filePath . '/';
        }

        return [
            'disk' => $disk = ($options['disk'] ?? $originalOptions['disk']),
            'visibility' => config('filesystems.disks.' . $disk . '.driver') != 'local' ? ($options['visibility'] ?? $originalOptions['visibility']) : null,
            'path' => str_replace(
                '/',
                DIRECTORY_SEPARATOR,
                $path . date('Y-m-d', time()) . '/'
            ),
        ];
    }

    /**
     * Upload Thumbnail.
     *
     * @param UploadedFile $file
     * @param array $options
     * @return Upload
     */
    protected function uploadThumbnail(UploadedFile $file, array $options = [])
    {
        $ratio  = $options['thumbnail']['ratio'] ?? false;
        $width  = $options['thumbnail']['width'] ?? null;
        $height = $options['thumbnail']['height'] ?? null;
        unset($options['thumbnail']);

        if (!$width && !$height) {
            throw new \Exception('You have to set width or height thumbnail\'s option.');
        }

        $thumbnail = Image::make($file);

        // Ratio or Fixed
        if ($ratio) {
            $thumbnail->resize(
                $width ?? null,
                $width > 0 ? null : $height,
                fn($constraint) => $constraint->aspectRatio()
            );
        } else {
            $thumbnail->resize($width, $height);
        }

        // Create Fake file
        $name = Str::random(37) . '.' . $file->extension();

        File::ensureDirectoryExists(storage_path('app/public/pharaonic-thumbs'));
        $thumbnail->save(storage_path('app/public/pharaonic-thumbs/' . $name), 100);

        $file = $this->handle(
            new UploadedFile(
                storage_path('app/public/pharaonic-thumbs/' . $name),
                $file->getClientOriginalName() . '-thumbnail',
                $thumbnail->mime()
            ),
            $options
        );

        // Delete Fake File
        File::delete(storage_path('app/public/pharaonic-thumbs/' . $name));

        return $file;
    }
}
