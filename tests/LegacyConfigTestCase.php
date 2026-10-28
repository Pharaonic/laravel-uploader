<?php

namespace Pharaonic\Laravel\Uploader\Tests;

abstract class LegacyConfigTestCase extends TestCase
{
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // Before the providers register: what Laravel loads from config/Pharaonic/uploader.php and config/pharaonic/uploader.php.
        $app['config']->set('Pharaonic.uploader', ['disk' => 'public', 'expire' => 60, 'uri' => '/legacy']);
        $app['config']->set('pharaonic.uploader', ['expire' => 90]);
    }
}
