<?php

namespace Pharaonic\Laravel\Uploader\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Pharaonic\Laravel\Uploader\Concerns\HasUrls;
use Pharaonic\Laravel\Uploader\Concerns\HasVisibility;

/**
 * @property int $id
 * @property int|null $thumbnail_id
 * @property string $hash
 * @property string $name
 * @property string $path
 * @property int $size
 * @property string $extension
 * @property string $mime
 * @property string $disk
 * @property string|null $visibility
 * @property-read string $url
 * @property-read string $temporaryUrl
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read Upload|null $thumbnail
 */
class Upload extends Model
{
    use HasUrls;
    use HasVisibility;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'thumbnail_id',
        'hash',
        'name',
        'path',
        'size',
        'extension',
        'mime',
        'disk',
        'visibility',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Get readable size.
     *
     * @return string|null
     */
    public function size(bool $decimal = true)
    {
        return readable_file_size($this->size, $decimal);
    }

    /**
     * Getting Thumbnail Object
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function thumbnail()
    {
        return $this->hasOne(static::class, 'id', 'thumbnail_id');
    }

    /**
     * Get the file URL or other information based on the target.
     *
     * @return string|array<mixed>
     */
    public function info(string $target = 'url', bool $isTemporary = false, ?int $expire = null)
    {
        $url = $isTemporary ? $this->temporaryUrl($expire) : $this->url;

        if ($target == 'url') {
            return $url;
        }

        $content = $this->extension === 'svg' ? Storage::disk($this->disk)->get($this->path) : null;
        $thumbnail = $this->relationLoaded('thumbnail') && $this->thumbnail
            ? $this->thumbnail->info($target, $isTemporary, $expire)
            : null;

        return $this->only('id', 'name', 'extension', 'mime') + [
            'size' => $this->size(),
            'url' => $url,
            'content' => $content,
            'thumbnail' => $thumbnail,
        ];
    }
}
