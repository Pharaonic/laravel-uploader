<?php

use Pharaonic\Laravel\Uploader\Facades\Uploader;

test('a config file published by 4.x still applies', function () {
    expect(config('pharaonic.uploader.disk'))->toBe('public')
        ->and(config('pharaonic.uploader.uri'))->toBe('/legacy')
        ->and(Uploader::options()['disk'])->toBe('public');
});

test('the canonical config file takes precedence', function () {
    expect(config('pharaonic.uploader.expire'))->toBe(90)
        ->and(config('Pharaonic.uploader.expire'))->toBe(90);
});

test('missing keys fall back to the package defaults', function () {
    expect(config('pharaonic.uploader.visibility'))->toBe('private')
        ->and(config('pharaonic.uploader.extensional'))->toBeTrue();
});
