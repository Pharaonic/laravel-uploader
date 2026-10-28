<?php

use Pharaonic\Laravel\Uploader\Support\Disposition;

test('it picks the disposition type', function (string $mime, string $type) {
    expect(Disposition::type($mime))->toBe($type);
})->with([
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
]);

test('it escapes the filename', function () {
    expect(Disposition::make('attachment', 'a "b".txt'))->toBe('attachment; filename="a \"b\".txt"')
        ->and(Disposition::make('inline', 'a/b\\c.txt'))->toBe('inline; filename=a_b_c.txt')
        ->and(Disposition::make('inline', ''))->toBe('inline; filename=file');
});

test('it adds an ASCII fallback for unicode names', function () {
    expect(Disposition::make('inline', 'café.txt'))->toBe("inline; filename=cafe.txt; filename*=utf-8''caf%C3%A9.txt");
});
