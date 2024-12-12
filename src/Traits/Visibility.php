<?php

namespace Pharaonic\Laravel\Uploader\Traits;

trait Visibility
{
    /**
     * Set Visibility
     *
     * @param string $visibility
     * @return static
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
     * @return static
     */
    public function public()
    {
        return $this->visibility('public');
    }

    /**
     * Set Visibility to Private
     *
     * @return static
     */
    public function private()
    {
        return $this->visibility('private');
    }
}
