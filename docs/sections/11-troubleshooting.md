## Troubleshooting

### Thumbnails are not created

The `thumbnail` option only applies to images other than SVG. Check that the file's MIME type starts with `image/`, and that GD or Imagick is installed: `php -m | grep -Ei 'gd|imagick'`.

### "Unsupported image type"

The image driver cannot read the format, such as TIFF or HEIC with GD. Switch Intervention Image to Imagick, or upload without the `thumbnail` option.

### "You have to set width or height thumbnail's option."

The `thumbnail` option needs a `width`, a `height`, or both. Nothing was stored.

### "Visibility is only available for S3."

`visibility()`, `public()` and `private()` only work on disks that do not use the local driver. Files on local disks have a `null` visibility.

### "The driver local does not support temporary URLs."

`temporaryUrl()` needs a disk that can sign URLs, such as S3, or a local disk that Laravel serves (`'serve' => true` in its disk config). For local files, `url()` points to the package file route instead.

### The file route returns 404

- The hash does not exist in the `uploads` table, or the file was deleted from the disk.
- You changed `uri` or `controller` after `php artisan route:cache`. Run `php artisan route:clear`.

### A file downloads instead of opening in the browser

The route serves SVG, HTML, XML, JavaScript and unknown types as attachments so they cannot run script on your domain. Images, audio, video, PDF and plain text open inline. An SVG still renders in an `<img>` tag.

### The stored extension differs from the original name

The extension and MIME type are detected from the file contents, not the client name, so `photo.JPG` may be stored as `jpeg`. The original name is kept in `name`.

### My published config is ignored

From `6.x`, the configuration file is `config/pharaonic/uploader.php`. A 4.x file in `config/Pharaonic/uploader.php` still applies, but the new file wins when both exist. Run `php artisan config:clear` after moving it.
