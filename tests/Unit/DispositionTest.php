<?php

namespace Pharaonic\Laravel\Uploader\Tests\Unit;

use Pharaonic\Laravel\Uploader\Support\Disposition;
use PHPUnit\Framework\TestCase;

class DispositionTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function types(): array
    {
        return [
            'jpeg' => ['image/jpeg', 'inline'],
            'png with parameters' => ['IMAGE/PNG; charset=binary', 'inline'],
            'pdf' => ['application/pdf', 'inline'],
            'plain text' => ['text/plain', 'inline'],
            'video' => ['video/mp4', 'inline'],
            'audio' => ['audio/mpeg', 'inline'],
            'svg' => ['image/svg+xml', 'attachment'],
            'html' => ['text/html', 'attachment'],
            'xhtml' => ['application/xhtml+xml', 'attachment'],
            'xml' => ['text/xml', 'attachment'],
            'javascript' => ['application/javascript', 'attachment'],
            'unknown' => ['application/octet-stream', 'attachment'],
            'empty' => ['', 'attachment'],
        ];
    }

    /**
     * @dataProvider types
     */
    public function test_it_picks_the_disposition_type(string $mime, string $type): void
    {
        $this->assertSame($type, Disposition::type($mime));
    }

    public function test_it_escapes_the_filename(): void
    {
        $this->assertSame('attachment; filename="a \"b\".txt"', Disposition::make('attachment', 'a "b".txt'));
        $this->assertSame('inline; filename=a_b_c.txt', Disposition::make('inline', 'a/b\\c.txt'));
        $this->assertSame('inline; filename=file', Disposition::make('inline', ''));
    }

    public function test_it_adds_an_ascii_fallback_for_unicode_names(): void
    {
        $this->assertSame(
            "inline; filename=cafe.txt; filename*=utf-8''caf%C3%A9.txt",
            Disposition::make('inline', 'café.txt')
        );
    }
}
