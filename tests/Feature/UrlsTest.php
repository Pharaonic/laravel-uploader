<?php

namespace Pharaonic\Laravel\Uploader\Tests\Feature;

use DateTimeInterface;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Mockery;
use Mockery\MockInterface;
use Pharaonic\Laravel\Uploader\Models\Upload;
use Pharaonic\Laravel\Uploader\Tests\TestCase;
use RuntimeException;

class UrlsTest extends TestCase
{
    public function test_a_local_file_url_points_to_the_file_route_with_its_extension(): void
    {
        $upload = $this->record(['disk' => 'local']);

        $this->assertSame('http://localhost/file/'.$upload->hash.'.pdf', $upload->url());
        $this->assertSame($upload->url(), $upload->url);
    }

    public function test_a_local_file_url_can_leave_out_the_extension(): void
    {
        config(['pharaonic.uploader.extensional' => false]);
        $this->app->forgetInstance('pharaonic.uploader');
        \Pharaonic\Laravel\Uploader\Facades\Uploader::clearResolvedInstances();

        $upload = $this->record(['disk' => 'local']);

        $this->assertSame('http://localhost/file/'.$upload->hash, $upload->url());
    }

    public function test_a_public_disk_file_url_comes_from_the_disk(): void
    {
        $upload = $this->record(['disk' => 'public']);

        $this->assertSame(Storage::disk('public')->url($upload->path), $upload->url());
    }

    public function test_a_local_file_has_no_temporary_url(): void
    {
        $upload = $this->record(['disk' => 'local']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The driver local does not support temporary URLs.');

        $upload->temporaryUrl();
    }

    public function test_a_private_s3_file_url_is_a_temporary_url(): void
    {
        Carbon::setTestNow('2026-01-15 12:00:00');
        $upload = $this->record(['disk' => 's3', 'visibility' => 'private', 'name' => 'Report "final".pdf']);

        $this->s3()->shouldReceive('temporaryUrl')
            ->once()
            ->withArgs(function (string $path, DateTimeInterface $expiration, array $options) use ($upload) {
                return $path === $upload->path
                    && $expiration->getTimestamp() === Carbon::parse('2026-01-15 12:30:00')->getTimestamp()
                    && $options === ['ResponseContentDisposition' => 'inline; filename="Report \"final\".pdf"'];
            })
            ->andReturn('https://s3.test/signed');

        $this->assertSame('https://s3.test/signed', $upload->url());
        Carbon::setTestNow();
    }

    public function test_a_temporary_url_accepts_an_expiry_in_minutes(): void
    {
        Carbon::setTestNow('2026-01-15 12:00:00');
        $upload = $this->record(['disk' => 's3', 'visibility' => 'public']);

        $this->s3()->shouldReceive('temporaryUrl')
            ->once()
            ->withArgs(fn (string $path, DateTimeInterface $expiration) => $expiration->getTimestamp() === Carbon::parse('2026-01-15 12:05:00')->getTimestamp())
            ->andReturn('https://s3.test/signed');

        $this->assertSame('https://s3.test/signed', $upload->temporaryUrl(5));
        Carbon::setTestNow();
    }

    public function test_a_public_s3_file_url_is_the_disk_url(): void
    {
        $upload = $this->record(['disk' => 's3', 'visibility' => 'public']);

        $this->s3()->shouldReceive('url')->once()->with($upload->path)->andReturn('https://s3.test/file');

        $this->assertSame('https://s3.test/file', $upload->url());
    }

    public function test_visibility_cannot_be_set_on_a_local_disk(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Visibility is only available for S3.');

        $this->record(['disk' => 'local'])->public();
    }

    public function test_saving_a_new_visibility_applies_it_to_the_stored_file(): void
    {
        $upload = $this->record(['disk' => 's3', 'visibility' => 'private']);

        $this->s3()->shouldReceive('setVisibility')->once()->with($upload->path, 'public')->andReturn(true);

        $upload->public()->save();

        $this->assertSame('public', $upload->fresh()->visibility);
    }

    public function test_info_returns_the_url_or_the_file_details(): void
    {
        $upload = $this->record(['disk' => 'local', 'size' => 2048]);

        $this->assertSame($upload->url, $upload->info());
        $this->assertSame([
            'id' => $upload->id,
            'name' => 'file.pdf',
            'extension' => 'pdf',
            'mime' => 'application/pdf',
            'size' => readable_file_size(2048),
            'url' => $upload->url,
            'content' => null,
            'thumbnail' => null,
        ], $upload->info('all'));
    }

    public function test_info_includes_svg_content_and_a_loaded_thumbnail(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('icon.svg', '<svg/>');

        $thumbnail = $this->record(['disk' => 'local']);
        $upload = $this->record(['disk' => 'local', 'path' => 'icon.svg', 'extension' => 'svg', 'mime' => 'image/svg+xml', 'thumbnail_id' => $thumbnail->id]);

        $info = $upload->load('thumbnail')->info('all');

        $this->assertIsArray($info);
        $this->assertSame('<svg/>', $info['content']);
        $this->assertSame($thumbnail->info('all'), $info['thumbnail']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function record(array $attributes = []): Upload
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
    private function s3()
    {
        /** @var FilesystemAdapter&MockInterface $disk */
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('providesTemporaryUrls')->andReturn(true)->byDefault();

        Storage::set('s3', $disk);

        return $disk;
    }
}
