<?php

namespace Pharaonic\Laravel\Uploader\Models;

use Illuminate\Database\Eloquent\Model;
use Pharaonic\Laravel\Uploader\Traits\Urls;
use Pharaonic\Laravel\Uploader\Traits\Visibility;

/**
 * Upload Model
 * 
 * @version 4.0
 * @author Moamen Eltouny (Raggi) <raggigroup@gmail.com>
 *
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
 * @property-read string url
 * @property-read string temporaryUrl
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 * @property-read Upload|null $thumbnail
 * @method string size(bool $decimal = true)
 * @method Upload visibility(string $visibility)
 * @method Upload public()
 * @method Upload private()
 * @method string url()
 * @method string temporaryUrl(int $expire = null)
 */
class Upload extends Model
{
    use Visibility;
    use Urls;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array
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
     * @var array
     */
    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Get readable size.
     *
     * @var bool $decimal
     */
    public function size(bool $decimal = true)
    {
        return ReadableSize($this->size, $decimal);
    }

    /**
     * Getting Thumbnail Object
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thumbnail()
    {
        return $this->hasOne(static::class, 'id', 'thumbnail_id');
    }

    /**
     * Get the file URL or other information based on the target.
     *
     * @param string $target
     * @param bool $isTemporary
     * @param int|null $expire
     * @return string|array|null
     */
    public function info(string $target = 'url', bool $isTemporary = false, int $expire = null)
    {
        if ($target == 'url') {
            return $isTemporary
                ? $this->temporaryUrl($expire)
                : $this->url;
        }

        return $this->only('id', 'name', 'extension', 'mime') + [
            'size' => $this->size(),
            'url' => $isTemporary
                ? $this->temporaryUrl($expire)
                : $this->url,
            'content' => in_array($this->extension, ['svg']) ? Storage::disk($this->disk)->get($this->path) : null,
        ];
    }
}
