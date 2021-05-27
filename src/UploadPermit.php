<?php

namespace Pharaonic\Laravel\Uploader;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Uploaded File Permissions Model
 *
 * @version 2.0
 * @author Moamen Eltouny (Raggi) <raggi@raggitech.com>
 */
class UploadPermit extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['upload_id', 'permitter_id', 'user_id', 'expiration'];

    /**
     * @var array
     */
    protected $dates = ['expiration'];

    /**
     * @var array
     */
    protected $casts = ['expiration' => 'datetime'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permitter()
    {
        return $this->belongsTo(config('Pharaonic.uploader.user', 'App\User'), 'permitter_id');
    }

    /**
     * Getting User Object
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(config('Pharaonic.uploader.user', 'App\User'), 'user_id');
    }

    /**
     * Getting File Object
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo(Upload::class);
    }

    /**
     * Check is Expired
     *
     * @return boolean
     */
    public function isExpired()
    {
        return $this->expiration != null && $this->expiration < Carbon::now();
    }
}
