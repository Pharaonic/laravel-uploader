<?php

namespace Pharaonic\Laravel\Uploader\Support;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;

/**
 * @internal
 */
final class Disposition
{
    /**
     * MIME types a browser may render inline without running script in the page origin.
     */
    private const INLINE = [
        'application/pdf',
        'image/apng',
        'image/avif',
        'image/bmp',
        'image/gif',
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/x-icon',
        'image/vnd.microsoft.icon',
        'text/plain',
    ];

    /**
     * Get the disposition type for a MIME type: inline when safe, attachment otherwise.
     */
    public static function type(string $mime): string
    {
        $mime = strtolower(trim(explode(';', $mime)[0]));

        if (in_array($mime, self::INLINE, true) || str_starts_with($mime, 'audio/') || str_starts_with($mime, 'video/')) {
            return 'inline';
        }

        return 'attachment';
    }

    /**
     * Build an escaped Content-Disposition header value.
     */
    public static function make(string $type, string $filename): string
    {
        $filename = str_replace(['/', '\\'], '_', $filename);
        $fallback = (string) preg_replace('/[^\x20-\x7e]|%/', '_', Str::ascii($filename));

        return HeaderUtils::makeDisposition($type, $filename !== '' ? $filename : 'file', $fallback !== '' ? $fallback : 'file');
    }
}
