<?php

namespace Pharaonic\Laravel\Uploader\Tests\Feature;

use Pharaonic\Laravel\Uploader\Facades\Uploader;
use Pharaonic\Laravel\Uploader\Tests\TestCase;

class LegacyConfigTest extends TestCase
{
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // Before the providers register: what Laravel loads from config/Pharaonic/uploader.php and config/pharaonic/uploader.php.
        $app['config']->set('Pharaonic.uploader', ['disk' => 'public', 'expire' => 60, 'uri' => '/legacy']);
        $app['config']->set('pharaonic.uploader', ['expire' => 90]);
    }

    public function test_a_config_file_published_by_4x_still_applies(): void
    {
        $this->assertSame('public', config('pharaonic.uploader.disk'));
        $this->assertSame('/legacy', config('pharaonic.uploader.uri'));
        $this->assertSame('public', Uploader::options()['disk']);
    }

    public function test_the_canonical_config_file_takes_precedence(): void
    {
        $this->assertSame(90, config('pharaonic.uploader.expire'));
        $this->assertSame(90, config('Pharaonic.uploader.expire'));
    }

    public function test_missing_keys_fall_back_to_the_package_defaults(): void
    {
        $this->assertSame('private', config('pharaonic.uploader.visibility'));
        $this->assertTrue(config('pharaonic.uploader.extensional'));
    }
}
