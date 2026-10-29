<?php

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Pharaonic\Laravel\Uploader\Facades\Uploader;
use Pharaonic\Laravel\Uploader\Models\Upload;

/**
 * @param  array<string, mixed>  $attributes
 */
function uploadRecord(array $attributes = []): Upload
{
    $upload = new Upload($attributes + [
        'hash' => bin2hex(random_bytes(16)),
        'name' => 'file.pdf',
        'path' => 'files/file.pdf',
        'size' => 10,
        'extension' => 'pdf',
        'mime' => 'application/pdf',
    ]);
    $upload->save();

    return $upload;
}

/**
 * @return FilesystemAdapter&MockInterface
 */
function fakeS3Disk()
{
    /** @var FilesystemAdapter&MockInterface $disk */
    $disk = Mockery::mock(FilesystemAdapter::class);
    $disk->shouldReceive('providesTemporaryUrls')->andReturn(true)->byDefault();

    Storage::set('s3', $disk);

    return $disk;
}

test('a local file URL points to the file route with its extension', function () {
    $upload = uploadRecord(['disk' => 'local']);

    expect($upload->url())->toBe('http://localhost/file/'.$upload->hash.'.pdf')
        ->and($upload->url)->toBe($upload->url());
});

test('a local file URL can leave out the extension', function () {
    config(['pharaonic.uploader.extensional' => false]);
    $this->app->forgetInstance('pharaonic.uploader');
    Uploader::clearResolvedInstances();

    $upload = uploadRecord(['disk' => 'local']);

    expect($upload->url())->toBe('http://localhost/file/'.$upload->hash);
});

test('a public disk file URL comes from the disk', function () {
    $upload = uploadRecord(['disk' => 'public']);

    expect($upload->url())->toBe(Storage::disk('public')->url($upload->path));
});

test('a local file has no temporary URL', function () {
    config(['filesystems.disks.local.serve' => false]);

    uploadRecord(['disk' => 'local'])->temporaryUrl();
})->throws(RuntimeException::class, 'The driver local does not support temporary URLs.');

test('a local disk that builds temporary URLs gives a temporary URL', function () {
    /** @var FilesystemAdapter $disk */
    $disk = Storage::disk('local');
    $disk->buildTemporaryUrlsUsing(fn (string $path) => 'https://signed.test/'.$path);

    expect(uploadRecord(['disk' => 'local'])->temporaryUrl())->toBe('https://signed.test/files/file.pdf');
});

test('a private S3 file URL is a temporary URL', function () {
    Carbon::setTestNow('2026-01-15 12:00:00');
    $upload = uploadRecord(['disk' => 's3', 'visibility' => 'private', 'name' => 'Report "final".pdf']);

    fakeS3Disk()->shouldReceive('temporaryUrl')
        ->once()
        ->withArgs(function (string $path, DateTimeInterface $expiration, array $options) use ($upload) {
            return $path === $upload->path
                && $expiration->getTimestamp() === Carbon::parse('2026-01-15 12:30:00')->getTimestamp()
                && $options === ['ResponseContentDisposition' => 'inline; filename="Report \"final\".pdf"'];
        })
        ->andReturn('https://s3.test/signed');

    expect($upload->url())->toBe('https://s3.test/signed');
    Carbon::setTestNow();
});

test('a temporary URL accepts an expiry in minutes', function () {
    Carbon::setTestNow('2026-01-15 12:00:00');
    $upload = uploadRecord(['disk' => 's3', 'visibility' => 'public']);

    fakeS3Disk()->shouldReceive('temporaryUrl')
        ->once()
        ->withArgs(fn (string $path, DateTimeInterface $expiration) => $expiration->getTimestamp() === Carbon::parse('2026-01-15 12:05:00')->getTimestamp())
        ->andReturn('https://s3.test/signed');

    expect($upload->temporaryUrl(5))->toBe('https://s3.test/signed');
    Carbon::setTestNow();
});

test('a public S3 file URL is the disk URL', function () {
    $upload = uploadRecord(['disk' => 's3', 'visibility' => 'public']);

    fakeS3Disk()->shouldReceive('url')->once()->with($upload->path)->andReturn('https://s3.test/file');

    expect($upload->url())->toBe('https://s3.test/file');
});

test('visibility cannot be set on a local disk', function () {
    uploadRecord(['disk' => 'local'])->public();
})->throws(LogicException::class, 'Visibility is only available for S3.');

test('saving a new visibility applies it to the stored file', function () {
    $upload = uploadRecord(['disk' => 's3', 'visibility' => 'private']);

    fakeS3Disk()->shouldReceive('setVisibility')->once()->with($upload->path, 'public')->andReturn(true);

    $upload->public()->save();

    expect($upload->fresh()->visibility)->toBe('public');
});

test('info returns the URL or the file details', function () {
    $upload = uploadRecord(['disk' => 'local', 'size' => 2048]);

    expect($upload->info())->toBe($upload->url)
        ->and($upload->info('all'))->toBe([
            'id' => $upload->id,
            'name' => 'file.pdf',
            'extension' => 'pdf',
            'mime' => 'application/pdf',
            'size' => readable_file_size(2048),
            'url' => $upload->url,
            'content' => null,
            'thumbnail' => null,
        ]);
});

test('info includes SVG content and a loaded thumbnail', function () {
    Storage::fake('local');
    Storage::disk('local')->put('icon.svg', '<svg/>');

    $thumbnail = uploadRecord(['disk' => 'local']);
    $upload = uploadRecord(['disk' => 'local', 'path' => 'icon.svg', 'extension' => 'svg', 'mime' => 'image/svg+xml', 'thumbnail_id' => $thumbnail->id]);

    $info = $upload->load('thumbnail')->info('all');

    expect($info)->toBeArray()
        ->and($info['content'])->toBe('<svg/>')
        ->and($info['thumbnail'])->toBe($thumbnail->info('all'));
});
