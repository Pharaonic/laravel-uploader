<?php

namespace Pharaonic\Laravel\Uploader;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Uploaded File Permissions Model
 *
 * @version 1.0
 * @author Raggi <support@pharaonic.io>
 * @license http://opensource.org/licenses/mit-license.php MIT License
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(config('Pharaonic.uploader.user', 'App\User'), 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo(Upload::class);
    }

    /**
     * Check is Expired
     */
    public function isExpired()
    {
        return $this->expiration != null && $this->expiration < Carbon::now();
    }
}
