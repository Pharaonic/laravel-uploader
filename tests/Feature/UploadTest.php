<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exception\NotReadableException;
use Pharaonic\Laravel\Uploader\Facades\Uploader;
use Pharaonic\Laravel\Uploader\Models\Upload;

beforeEach(function () {
    Storage::fake('local');
});

test('it uploads a file and stores its record', function () {
    $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'));

    expect($upload->exists)->toBeTrue()
        ->and(strlen($upload->hash))->toBe(67)
        ->and($upload->disk)->toBe('local')
        ->and($upload->visibility)->toBeNull()
        ->and($upload->name)->toBe('notes.txt')
        ->and($upload->extension)->toBe('txt')
        ->and($upload->mime)->toBe('text/plain')
        ->and($upload->size)->toBe(5)
        ->and($upload->path)->toBe('/'.date('Y-m-d').'/'.$upload->hash.'.txt')
        ->and(Storage::disk('local')->get($upload->path))->toBe('Hello');
});

test('it uses forward slashes and the path option', function () {
    config(['pharaonic.uploader.path' => '/uploads/']);
    $this->app->forgetInstance('pharaonic.uploader');
    Uploader::clearResolvedInstances();

    $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'), ['path' => '/users/1/']);

    expect($upload->path)->toBe('uploads/users/1/'.date('Y-m-d').'/'.$upload->hash.'.txt');
    Storage::disk('local')->assertExists($upload->path);
});

test('it uploads to the disk option', function () {
    Storage::fake('public');

    $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'), ['disk' => 'public']);

    expect($upload->disk)->toBe('public')
        ->and($upload->visibility)->toBeNull();
    Storage::disk('public')->assertExists($upload->path);
});

test('it uploads a file from a path', function () {
    $path = $this->temporaryFile('report.txt', 'Report');

    $upload = Uploader::upload($path);

    expect($upload->name)->toBe('report.txt')
        ->and($upload->extension)->toBe('txt')
        ->and(Storage::disk('local')->get($upload->path))->toBe('Report')
        ->and($path)->toBeFile();
});

test('it rejects a missing path', function () {
    Uploader::upload('/does/not/exist.txt');
})->throws(InvalidArgumentException::class, 'The file path does not exist.');

test('the helpers upload and find files', function () {
    $upload = upload($this->realFile('notes.txt', 'Hello'));

    expect($upload)->toBeInstanceOf(Upload::class)
        ->and(uploaded($upload->hash)->is($upload))->toBeTrue();
});

test('uploaded throws for an unknown hash', function () {
    uploaded('unknown');
})->throws(ModelNotFoundException::class);

test('it generates a thumbnail for an image', function () {
    $this->requiresGd();

    $upload = Uploader::upload(UploadedFile::fake()->image('photo.jpg', 400, 200), [
        'thumbnail' => ['width' => 100, 'ratio' => true],
    ]);

    $thumbnail = $upload->thumbnail;

    expect($thumbnail)->toBeInstanceOf(Upload::class)
        ->and($thumbnail->extension)->toBeIn(['jpg', 'jpeg'])
        ->and($thumbnail->name)->toBe('photo-thumbnail.'.$thumbnail->extension)
        ->and($thumbnail->mime)->toBe('image/jpeg')
        ->and(array_slice((array) getimagesizefromstring((string) Storage::disk('local')->get($thumbnail->path)), 0, 2))->toBe([100, 50])
        ->and($thumbnail->thumbnail_id)->toBeNull();
});

test('the thumbnail name ends with the thumbnail extension', function () {
    $this->requiresGd();

    $jpeg = Uploader::upload(UploadedFile::fake()->image('my.photo.jpeg', 40, 40), ['thumbnail' => ['width' => 10]]);

    ob_start();
    imagepng(imagecreatetruecolor(40, 40));
    $png = Uploader::upload($this->realFile('scan', (string) ob_get_clean()), ['thumbnail' => ['width' => 10]]);

    // Older Symfony releases guess "jpeg" for image/jpeg, newer ones "jpg".
    expect($jpeg->thumbnail->name)->toBe('my.photo-thumbnail.'.$jpeg->thumbnail->extension)
        ->and($png->thumbnail->name)->toBe('scan-thumbnail.png');

    $this->get($png->thumbnail->url)->assertHeader('Content-Disposition', 'inline; filename=scan-thumbnail.png');
});

test('it resizes a thumbnail to a fixed size', function () {
    $this->requiresGd();

    $upload = Uploader::upload(UploadedFile::fake()->image('photo.png', 400, 200), [
        'thumbnail' => ['width' => 50, 'height' => 50],
    ]);

    expect(array_slice((array) getimagesizefromstring((string) Storage::disk('local')->get($upload->thumbnail->path)), 0, 2))->toBe([50, 50]);
});

test('a thumbnail without a size is rejected before anything is stored', function () {
    $this->requiresGd();

    expect(fn () => Uploader::upload(UploadedFile::fake()->image('photo.jpg'), ['thumbnail' => ['ratio' => true]]))
        ->toThrow(InvalidArgumentException::class, 'You have to set width or height thumbnail\'s option.');

    expect(Upload::query()->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBe([]);
});

test('a thumbnail the image driver cannot read leaves nothing stored', function () {
    $this->requiresGd();

    $file = UploadedFile::fake()->create('scan.tiff', 1, 'image/tiff');

    // The original is stored before the thumbnail fails, then removed.
    expect(fn () => Uploader::upload($file, ['thumbnail' => ['width' => 10]]))->toThrow(NotReadableException::class);

    expect(Upload::query()->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBe([]);
});

test('it ignores the thumbnail option for files that are not raster images', function () {
    $text = Uploader::upload($this->realFile('notes.txt', 'Hello'), ['thumbnail' => ['width' => 10]]);
    $svg = Uploader::upload($this->realFile('icon.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>'), ['thumbnail' => ['width' => 10]]);

    expect($text->thumbnail_id)->toBeNull()
        ->and($svg->thumbnail_id)->toBeNull()
        ->and(Upload::query()->count())->toBe(2);
});

test('deleting an upload deletes its file and thumbnail', function () {
    $this->requiresGd();

    $upload = Uploader::upload(UploadedFile::fake()->image('photo.jpg', 40, 40), ['thumbnail' => ['width' => 10]]);
    $thumbnail = $upload->thumbnail;

    $upload->delete();

    Storage::disk('local')->assertMissing($upload->path);
    Storage::disk('local')->assertMissing($thumbnail->path);
    expect(Upload::query()->count())->toBe(0);
});

test('deleting an upload whose file is gone still deletes the record', function () {
    $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'));
    Storage::disk('local')->delete($upload->path);

    $upload->delete();

    expect(Upload::query()->count())->toBe(0);
});

test('it returns a readable size', function () {
    $upload = Uploader::upload($this->realFile('notes.txt', str_repeat('a', 2048)));

    expect($upload->size())->toBe(readable_file_size(2048))
        ->and($upload->size(false))->toBe(readable_file_size(2048, false));
});
