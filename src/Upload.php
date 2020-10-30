<?php

namespace Pharaonic\Laravel\Uploader;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Pharaonic\Laravel\Readable\Readable;

class Upload extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $fillable = [
        'uploader_id',
        'hash', 'name', 'path', 'size', 'extension', 'mime',
        'visitable', 'visits',
        'private'
    ];

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
        $options = (object) array_merge(config('Pharaonic.uploader.options', []), $options);

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

        // Delete Old File
        if (isset($options->file)) {

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
            ]);

            return $options->file;
        } else {
            return Upload::create([
                'name'          => $name,
                'hash'          => $hash,
                'path'          => $path . DIRECTORY_SEPARATOR . $hash . $plusName,
                'extension'     => $extension,
                'mime'          => $mime,
                'size'          => $size,
                'visitable'     => $options->visitable,
                'private'       => $options->private,
                'uploader_id'   => auth()->check() ? auth()->user()->id : null
            ]);
        }
    }

    /**
     * Delete File
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
     * Get Url
     */
    public function getUrlAttribute()
    {
        return route('uploaded', $this->hash);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function uploader()
    {
        return $this->belongsTo(config('Pharaonic.uploader.user', 'App\User'), 'uploader_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permits()
    {
        return $this->hasMany(UploadPermit::class);
    }

    /**
     * Is Permitted?
     */
    public function isPermitted(Model $user)
    {
        if (get_class($user) != config('Pharaonic.uploader.user', 'App\User')) return false;

        $permit = UploadPermit::where('upload_id', $this->id)->where('user_id', $user->id)->first();
        return $permit != null;
    }

    /**
     * Permit for a Private File
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
     */
    public function forbid(Model $user)
    {
        if (get_class($user) != config('Pharaonic.uploader.user', 'App\User')) return false;

        $permit = UploadPermit::where('upload_id', $this->id)->where('user_id', $user->id)->first();
        if ($permit) return $permit->delete();

        return false;
    }
}
