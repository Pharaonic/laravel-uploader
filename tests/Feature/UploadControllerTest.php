<?php

namespace Pharaonic\Laravel\Uploader\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Pharaonic\Laravel\Uploader\Facades\Uploader;
use Pharaonic\Laravel\Uploader\Tests\TestCase;

class UploadControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_it_streams_a_file_inline_with_safe_headers(): void
    {
        $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'));

        $response = $this->get('/file/'.$upload->hash.'.txt');

        $response->assertOk();
        $this->assertSame('Hello', $response->streamedContent());
        $this->assertStringStartsWith('text/plain', (string) $response->headers->get('Content-Type'));
        $response->assertHeader('Content-Disposition', 'inline; filename=notes.txt');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame([], $response->headers->getCookies());
    }

    public function test_it_finds_a_file_without_the_extension(): void
    {
        $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'));

        $this->assertSame('Hello', $this->get('/file/'.$upload->hash)->streamedContent());
    }

    public function test_it_downloads_types_that_can_run_script(): void
    {
        $svg = Uploader::upload($this->realFile('icon.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>'));
        $html = Uploader::upload($this->realFile('page.html', '<html><body>Hi</body></html>'));

        $this->get($svg->url)->assertHeader('Content-Disposition', 'attachment; filename=icon.svg');
        $this->get($html->url)->assertHeader('Content-Disposition', 'attachment; filename=page.html');
    }

    public function test_it_returns_not_found_for_an_unknown_hash(): void
    {
        $this->get('/file/unknown.txt')->assertNotFound();
    }

    public function test_it_returns_not_found_when_the_file_is_gone(): void
    {
        $upload = Uploader::upload($this->realFile('notes.txt', 'Hello'));
        Storage::disk('local')->delete($upload->path);

        $this->get($upload->url)->assertNotFound();
    }
}
