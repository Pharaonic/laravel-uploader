<?php

namespace Pharaonic\Laravel\Uploader\Concerns;

use Illuminate\Support\Facades\Config;
use LogicException;

trait HasVisibility
{
    /**
     * Set the visibility. Call save() to apply it to the stored file.
     *
     * @return static
     *
     * @throws LogicException on a disk using the local driver.
     */
    public function visibility(string $visibility)
    {
        if (Config::get('filesystems.disks.'.$this->disk.'.driver') === 'local') {
            throw new LogicException('Visibility is only available for S3.');
        }

        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Set the visibility to public.
     *
     * @return static
     */
    public function public()
    {
        return $this->visibility('public');
    }

    /**
     * Set the visibility to private.
     *
     * @return static
     */
    public function private()
    {
        return $this->visibility('private');
    }
}
