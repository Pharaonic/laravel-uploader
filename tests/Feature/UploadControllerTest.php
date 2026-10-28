<?php

use Illuminate\Support\Facades\Storage;
use Pharaonic\Laravel\Uploader\Facades\Uploader;

beforeEach(function () {
    Storage::fake('local');
});

test('it streams a file inline with safe headers', function () {
    $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'));

    $response = $this->get('/file/'.$upload->hash.'.txt');

    $response->assertOk()
        ->assertHeader('Content-Disposition', 'inline; filename=notes.txt')
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    expect($response->streamedContent())->toBe('Hello')
        ->and((string) $response->headers->get('Content-Type'))->toStartWith('text/plain')
        ->and($response->headers->getCookies())->toBe([]);
});

test('it finds a file without the extension', function () {
    $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'));

    expect($this->get('/file/'.$upload->hash)->streamedContent())->toBe('Hello');
});

test('it downloads types that can run script', function () {
    $svg = Uploader::upload($this->realFile('icon.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>'));
    $html = Uploader::upload($this->realFile('page.html', '<html><body>Hi</body></html>'));

    $this->get($svg->url)->assertHeader('Content-Disposition', 'attachment; filename=icon.svg');
    $this->get($html->url)->assertHeader('Content-Disposition', 'attachment; filename=page.html');
});

test('it returns not found for an unknown hash', function () {
    $this->get('/file/unknown.txt')->assertNotFound();
});

test('it returns not found when the file is gone', function () {
    $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'));
    Storage::disk('local')->delete($upload->path);

    $this->get($upload->url)->assertNotFound();
});
