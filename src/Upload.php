<?php

namespace Pharaonic\Laravel\Uploader;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Pharaonic\Laravel\Readable\Readable;

/**
 * Upload Model
 *
 * @version 2.0
 * @author Moamen Eltouny (Raggi) <raggi@raggitech.com>
 */
class Upload extends Model
{
    /**
     * Fillable Columns
     * 
     * @var array
     */
    protected $fillable = [
        'uploader_id',
        'hash', 'name', 'path', 'size', 'extension', 'mime',
        'visitable', 'visits',
        'private', 'thumbnail_id'
    ];

    /**
     * Booting Upload
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::deleting(function ($upload) {
            $upload->deleteFile();
        });
    }

    /**
     * Upload File
     *
     * @var Illuminate\Http\UploadedFile $file
     * @var Upload|null $perv
     * @var array|null $options
     */
    public static function upload(UploadedFile $file, array $options = [])
    {
        $originalOptions = array_merge(config('Pharaonic.uploader.options', []), $options);
        $options = (object) $originalOptions;

        $name       =   $file->getClientOriginalName();
        $hash       =   $file->hashName();
        if (strpos($hash, '.') !== false) {
            $hash = explode('.', $hash);
            unset($hash[count($hash) - 1]);
            $hash = implode('', $hash);
        }

        $hash       =   uniqid(config('Pharaonic.uploader.options.prefix', ''), true) . Str::random(7)  . '-' . $hash;

        if (strpos($name, '.') !== false) {
            $ext = explode('.', $name);
            $ext = $ext[count($ext) - 1];
        } else {
            $ext = null;
        }

        $path       = (isset($options->directory) ? trim($options->directory, '/') . DIRECTORY_SEPARATOR : '') . date('Y-m-d', time());
        $main_path  =   trim(config('Pharaonic.uploader.path', ''), '/');
        if (!empty($main_path)) $path = $main_path . DIRECTORY_SEPARATOR . $path;

        $extension  =   $file->extension();
        $extension  =   $extension == $ext ? $ext : $ext;
        $mime       =   $file->getMimeType();
        $size       =   $file->getSize();

        // Save File
        $plusName =  'raggi.' . Str::random(20);
        $file->storeAs($path, $hash . $plusName, config('Pharaonic.uploader.disk', 'public'));

        // Thumbnail Generating
        $thumbnail = null;
        if (isset($options->thumbnail)) {
            $ratio  = $options->thumbnail['ratio'] ?? false;
            $width  = $options->thumbnail['width'] ?? null;
            $height = $options->thumbnail['height'] ?? null;
            if (!$width && !$height) throw new \Exception('You have to set width or height thumbnail\'s option.');

            $thumbnail = Image::make($file);

            // Ratio or Fixed
            if ($ratio) {
                $thumbnail->resize($width ?? null, $width > 0 ? null : $height, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } else {
                $thumbnail->resize($width, $height);
            }

            // Save Thumbnail before Upload it
            $thumb_name = Str::random(37) . '.' . $extension;
            if (!File::isDirectory(storage_path('app/public/pharaonic-thumbs'))) File::makeDirectory(storage_path('app/public/pharaonic-thumbs'));
            $thumbnail->save(storage_path('app/public/pharaonic-thumbs/' . $thumb_name), 100);

            $thumbnail = new UploadedFile(storage_path('app/public/pharaonic-thumbs/' . $thumb_name), $thumb_name, $thumbnail->mime());

            // Upload Thumbnail
            if (isset($originalOptions['thumbnail'])) unset($originalOptions['thumbnail']);
            if (isset($originalOptions['file'])) unset($originalOptions['file']);
            $originalOptions['directory'] = rtrim($originalOptions['directory'] ?? null, '/') . '/thumbnails';
            $thumbnail = upload($thumbnail, $originalOptions)->id;

            // Delete Fake File
            Storage::disk(config('Pharaonic.uploader.disk', 'public'))->delete('pharaonic-thumbs/' . $thumb_name);

        }

        // Create / Update
        if (isset($options->file)) {
            // Delete Old Files
            if ($options->file->thumbnail_id > 0) $options->file->thumbnail->delete();
            $options->file->deleteFile();

            $options->file->update([
                'name'          => $name,
                'hash'          => $hash,
                'path'          => $path . DIRECTORY_SEPARATOR . $hash . $plusName,
                'extension'     => $extension,
                'mime'          => $mime,
                'size'          => $size,
                'visitable'     => $options->visitable,
                'private'       => $options->private,
                'thumbnail_id'  => $thumbnail
            ]);

            return $options->file;
        } else {
            $file = Upload::create([
                'name'          => $name,
                'hash'          => $hash,
                'path'          => $path . DIRECTORY_SEPARATOR . $hash . $plusName,
                'extension'     => $extension,
                'mime'          => $mime,
                'size'          => $size,
                'visitable'     => $options->visitable,
                'private'       => $options->private,
                'thumbnail_id'  => $thumbnail,
                'uploader_id'   => auth()->check() ? auth()->user()->id : null
            ]);

            return $file;
        }
    }

    /**
     * Deleting File && Thumbnail
     *
     * @return boolean
     */
    public function deleteFile()
    {
        return Storage::disk(config('Pharaonic.uploader.disk', 'public'))->delete($this->path);
    }

    /**
     * Get Readable Size
     *
     * @var bool $decimal
     */
    public function readableSize(bool $decimal = true)
    {
        return ReadableSize($this->size, $decimal);
    }

    /**
     * Getting URL
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return route('uploaded', $this->hash);
    }

    /**
     * Getting Thumbnail Object
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thumbnail()
    {
        return $this->hasOne(self::class, 'id', 'thumbnail_id');
    }

    /**
     * Getting Uploader Object
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function uploader()
    {
        return $this->belongsTo(config('Pharaonic.uploader.user', 'App\User'), 'uploader_id');
    }

    /**
     * Getting Permits
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permits()
    {
        return $this->hasMany(UploadPermit::class);
    }

    /**
     * Is Permitted?
     *
     * @param Model $user
     * @return boolean
     */
    public function isPermitted(Model $user)
    {
        if (get_class($user) != config('Pharaonic.uploader.user', 'App\User')) return false;

        $permit = UploadPermit::where('upload_id', $this->id)->where('user_id', $user->id)->first();
        return $permit != null;
    }

    /**
     * Permit for a Private File
     *
     * @param Model $user
     * @param $expiration
     * @return Model|boolean
     */
    public function permit(Model $user, $expiration = null)
    {
        if (get_class($user) != config('Pharaonic.uploader.user', 'App\User')) return false;

        if ($expiration) Readable::prepareDateTime($expiration);

        $permit = UploadPermit::where('upload_id', $this->id)->where('user_id', $user->id)->first();

        if ($permit) {
            // Update
            return $permit->update(['expiration' => $expiration]);
        } else {
            // Create
            return UploadPermit::create([
                'upload_id'     => $this->id,
                'user_id'       => $user->id,
                'permitter_id'  => auth()->check() ? auth()->user()->id : null,
                'expiration'    => $expiration
            ]);
        }
    }

    /**
     * Forbid for a Private File
     *
     * @param Model $user
     * @return void
     */
    public function forbid(Model $user)
    {
        if (get_class($user) != config('Pharaonic.uploader.user', 'App\User')) return false;

        $permit = UploadPermit::where('upload_id', $this->id)->where('user_id', $user->id)->first();
        if ($permit) return $permit->delete();

        return false;
    }
}
