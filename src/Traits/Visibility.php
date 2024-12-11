<?php

namespace Pharaonic\Laravel\Uploader\Traits;

trait Visibility
{
    /**
     * Set Visibility
     *
     * @param string $visibility
     * @return $this
     */
    public function visibility(string $visibility)
    {
        if (config('filesystems.disks.' . $this->disk . '.driver') == 'local') {
            throw new \Exception('Visibility is only available for S3.');
        }
        
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Set Visibility to Public
     *
     * @return $this
     */
    public function public()
    {
        return $this->visibility('public');
    }

    /**
     * Set Visibility to Private
     *
     * @return $this
     */
    public function private()
    {
        return $this->visibility('private');
    }
}
