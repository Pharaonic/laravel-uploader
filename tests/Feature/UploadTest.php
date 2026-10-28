<?php

namespace Pharaonic\Laravel\Uploader\Tests\Feature;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exception\NotReadableException;
use InvalidArgumentException;
use Pharaonic\Laravel\Uploader\Facades\Uploader;
use Pharaonic\Laravel\Uploader\Models\Upload;
use Pharaonic\Laravel\Uploader\Tests\TestCase;

class UploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_it_uploads_a_file_and_stores_its_record(): void
    {
        $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'));

        $this->assertTrue($upload->exists);
        $this->assertSame(67, strlen($upload->hash));
        $this->assertSame('local', $upload->disk);
        $this->assertNull($upload->visibility);
        $this->assertSame('notes.txt', $upload->name);
        $this->assertSame('txt', $upload->extension);
        $this->assertSame('text/plain', $upload->mime);
        $this->assertSame(5, $upload->size);
        $this->assertSame('/'.date('Y-m-d').'/'.$upload->hash.'.txt', $upload->path);
        $this->assertSame('Hello', Storage::disk('local')->get($upload->path));
    }

    public function test_it_uses_forward_slashes_and_the_path_option(): void
    {
        config(['pharaonic.uploader.path' => '/uploads/']);
        $this->app->forgetInstance('pharaonic.uploader');
        Uploader::clearResolvedInstances();

        $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'), ['path' => '/users/1/']);

        $this->assertSame('uploads/users/1/'.date('Y-m-d').'/'.$upload->hash.'.txt', $upload->path);
        Storage::disk('local')->assertExists($upload->path);
    }

    public function test_it_uploads_to_the_disk_option(): void
    {
        Storage::fake('public');

        $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'), ['disk' => 'public']);

        $this->assertSame('public', $upload->disk);
        $this->assertNull($upload->visibility);
        Storage::disk('public')->assertExists($upload->path);
    }

    public function test_it_uploads_a_file_from_a_path(): void
    {
        $path = $this->temporaryFile('report.txt', 'Report');

        $upload = Uploader::upload($path);

        $this->assertSame('report.txt', $upload->name);
        $this->assertSame('txt', $upload->extension);
        $this->assertSame('Report', Storage::disk('local')->get($upload->path));
        $this->assertFileExists($path);
    }

    public function test_it_rejects_a_missing_path(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The file path does not exist.');

        Uploader::upload('/does/not/exist.txt');
    }

    public function test_the_helpers_upload_and_find_files(): void
    {
        $upload = upload($this->realFile('notes.txt', 'Hello'));

        $this->assertInstanceOf(Upload::class, $upload);
        $this->assertTrue(uploaded($upload->hash)->is($upload));
    }

    public function test_uploaded_throws_for_an_unknown_hash(): void
    {
        $this->expectException(ModelNotFoundException::class);

        uploaded('unknown');
    }

    public function test_it_generates_a_thumbnail_for_an_image(): void
    {
        $this->requiresGd();

        $upload = Uploader::upload(UploadedFile::fake()->image('photo.jpg', 400, 200), [
            'thumbnail' => ['width' => 100, 'ratio' => true],
        ]);

        $thumbnail = $upload->thumbnail;

        $this->assertInstanceOf(Upload::class, $thumbnail);
        $this->assertContains($thumbnail->extension, ['jpg', 'jpeg']);
        $this->assertSame('photo-thumbnail.'.$thumbnail->extension, $thumbnail->name);
        $this->assertSame('image/jpeg', $thumbnail->mime);
        $this->assertSame([100, 50], array_slice((array) getimagesizefromstring((string) Storage::disk('local')->get($thumbnail->path)), 0, 2));
        $this->assertNull($thumbnail->thumbnail_id);
    }

    public function test_the_thumbnail_name_ends_with_the_thumbnail_extension(): void
    {
        $this->requiresGd();

        $jpeg = Uploader::upload(UploadedFile::fake()->image('my.photo.jpeg', 40, 40), ['thumbnail' => ['width' => 10]]);
        ob_start();
        imagepng(imagecreatetruecolor(40, 40));
        $png = Uploader::upload($this->realFile('scan', (string) ob_get_clean()), ['thumbnail' => ['width' => 10]]);

        // Older Symfony releases guess "jpeg" for image/jpeg, newer ones "jpg".
        $this->assertSame('my.photo-thumbnail.'.$jpeg->thumbnail->extension, $jpeg->thumbnail->name);
        $this->assertSame('scan-thumbnail.png', $png->thumbnail->name);
        $this->get($png->thumbnail->url)->assertHeader('Content-Disposition', 'inline; filename=scan-thumbnail.png');
    }

    public function test_it_resizes_a_thumbnail_to_a_fixed_size(): void
    {
        $this->requiresGd();

        $upload = Uploader::upload(UploadedFile::fake()->image('photo.png', 400, 200), [
            'thumbnail' => ['width' => 50, 'height' => 50],
        ]);

        $this->assertSame([50, 50], array_slice((array) getimagesizefromstring((string) Storage::disk('local')->get($upload->thumbnail->path)), 0, 2));
    }

    public function test_a_thumbnail_without_a_size_is_rejected_before_anything_is_stored(): void
    {
        $this->requiresGd();

        try {
            Uploader::upload(UploadedFile::fake()->image('photo.jpg'), ['thumbnail' => ['ratio' => true]]);
            $this->fail('No exception was thrown.');
        } catch (InvalidArgumentException $e) {
            $this->assertSame('You have to set width or height thumbnail\'s option.', $e->getMessage());
        }

        $this->assertSame(0, Upload::query()->count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_a_thumbnail_the_image_driver_cannot_read_leaves_nothing_stored(): void
    {
        $this->requiresGd();

        $file = UploadedFile::fake()->create('scan.tiff', 1, 'image/tiff');

        try {
            Uploader::upload($file, ['thumbnail' => ['width' => 10]]);
            $this->fail('No exception was thrown.');
        } catch (NotReadableException $e) {
            // The original was stored before the thumbnail failed, then removed.
        }

        $this->assertSame(0, Upload::query()->count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_it_ignores_the_thumbnail_option_for_files_that_are_not_raster_images(): void
    {
        $text = Uploader::upload($this->realFile('notes.txt', 'Hello'), ['thumbnail' => ['width' => 10]]);
        $svg = Uploader::upload($this->realFile('icon.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>'), ['thumbnail' => ['width' => 10]]);

        $this->assertNull($text->thumbnail_id);
        $this->assertNull($svg->thumbnail_id);
        $this->assertSame(2, Upload::query()->count());
    }

    public function test_deleting_an_upload_deletes_its_file_and_thumbnail(): void
    {
        $this->requiresGd();

        $upload = Uploader::upload(UploadedFile::fake()->image('photo.jpg', 40, 40), ['thumbnail' => ['width' => 10]]);
        $thumbnail = $upload->thumbnail;

        $upload->delete();

        Storage::disk('local')->assertMissing($upload->path);
        Storage::disk('local')->assertMissing($thumbnail->path);
        $this->assertSame(0, Upload::query()->count());
    }

    public function test_deleting_an_upload_whose_file_is_gone_still_deletes_the_record(): void
    {
        $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'));
        Storage::disk('local')->delete($upload->path);

        $upload->delete();

        $this->assertSame(0, Upload::query()->count());
    }

    public function test_it_returns_a_readable_size(): void
    {
        $upload = Uploader::upload($this->realFile('notes.txt', str_repeat('a', 2048)));

        $this->assertSame(readable_file_size(2048), $upload->size());
        $this->assertSame(readable_file_size(2048, false), $upload->size(false));
    }
}
