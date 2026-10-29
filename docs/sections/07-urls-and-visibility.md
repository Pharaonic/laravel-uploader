## URLs & Visibility

`$upload->url` picks the right URL for the disk the file is on:

| Disk | URL |
| --- | --- |
| The disk named `local` | The package file route: `/file/{hash}.{extension}` |
| A private file on S3 (or any disk that supports temporary URLs) | A temporary URL, valid for `expire` minutes |
| Anything else, such as `public` or a public S3 file | The disk's own URL, from `Storage::disk($disk)->url($path)` |

```php
$upload->url;   // same as $upload->url()
```

### Temporary URLs

Ask for a temporary URL directly, with the default lifetime or your own, in minutes:

```php
$upload->temporaryUrl();    // valid for the configured `expire` minutes
$upload->temporaryUrl(5);   // valid for 5 minutes
$upload->temporary_url;     // attribute form, default lifetime
```

The URL makes the browser use the upload's original name. A disk that cannot create temporary URLs throws `RuntimeException`. A `local` disk creates them only when Laravel serves it (`'serve' => true` in its disk config).

### Visibility

On S3 and other disks that are not local, new files take the `visibility` from the configuration or the upload options. Change it later with `public()`, `private()` or `visibility()`, then save the model; the stored object is updated on save.

```php
$upload->public()->save();
$upload->private()->save();
$upload->visibility('public')->save();
```

On a disk that uses the local driver, these methods throw `LogicException`.

### File details for APIs

`info()` returns the URL, or with any other target, an array that is ready to return as JSON:

```php
$upload->info();      // the URL, like $upload->url
$upload->info('url', true, 10); // a temporary URL valid for 10 minutes

$upload->load('thumbnail')->info('details');
// [
//     'id' => 1,
//     'name' => 'photo.jpg',
//     'extension' => 'jpg',
//     'mime' => 'image/jpeg',
//     'size' => '1.2 MB',
//     'url' => 'https://example.com/file/Xb3....jpg',
//     'content' => null,      // the file contents for SVG files
//     'thumbnail' => [...],   // the same array for the thumbnail, when loaded
// ]
```

:::warning SVG content
`info()` returns the content of SVG files so you can inline them. Only inline SVG files from trusted sources: an SVG can contain script.
:::
