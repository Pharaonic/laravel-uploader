<?php

namespace Pharaonic\Laravel\Uploader\Traits;

trait Urls
{
    /**
     * Getting url.
     *
     * @return string
     */
    public function url()
    {
        
    }

    /**
     * Getting temporary url.
     *
     * @return string
     */
    public function temporaryUrl(int $expire = null)
    {
        // 
    }

    /**
     * Getting temporary url.
     *
     * @return string
     */
    public function getTemporaryUrlAttribute()
    {
        return $this->temporaryUrl();
    }

    /**
     * Getting url.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return $this->url();
    }
}
