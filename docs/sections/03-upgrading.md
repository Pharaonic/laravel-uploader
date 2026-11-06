## Upgrading from 13.0

13.1 generates thumbnails with Intervention Image 4 instead of 2. The package's own API, options and stored files do not change.

- If your application calls Intervention Image 2 itself (`Image::make()`, `Intervention\Image\Facades\Image`), it now has to require `intervention/image` 2 on its own, which Composer cannot install next to 13.1. Move that code to Intervention Image 4 or to Laravel's `Illuminate\Image` component, or stay on 13.0.
- The Imagick driver is chosen with `IMAGE_DRIVER=imagick` (`images.default`, Laravel 13.21+). A published `config/image.php` from Intervention Image 2 is no longer read, and can be deleted.
- An image the driver cannot read throws `Intervention\Image\Exceptions\ImageDecoderException` instead of `Intervention\Image\Exception\NotReadableException`.

## Upgrading from 4.x

The `13.x` line runs on Laravel 13 with PHP 8.3, 8.4 or 8.5. Most applications upgrade without code changes; check the points below.

### Database

A new migration makes the `hash` column unique. Run it after updating:

```bash title="Terminal" no-line-numbers
php artisan migrate
```

### Configuration

The configuration now lives under `pharaonic.uploader` and publishes to `config/pharaonic/uploader.php`. A file you published from 4.x to `config/Pharaonic/uploader.php` is still applied, and `config('Pharaonic.uploader')` still returns the options, but both are deprecated. Move your file to the new path:

```bash title="Terminal" no-line-numbers
php artisan vendor:publish --tag=uploader-config
```

When both files exist, `config/pharaonic/uploader.php` wins.

### Model traits

`Pharaonic\Laravel\Uploader\Traits\Urls` and `Traits\Visibility` were removed. If one of your own models used them, switch to the new names:

```php title="app/Models/Attachment.php"
use Pharaonic\Laravel\Uploader\Concerns\HasUrls;
use Pharaonic\Laravel\Uploader\Concerns\HasVisibility;
```

### The file route

The route keeps its name (`uploader.file`) and URI (`/file/{hash}`), so stored links keep working. It no longer runs the `web` middleware group: it starts no session and sets no cookies. SVG, HTML, XML, JavaScript and unknown types are sent with `Content-Disposition: attachment`, so a browser downloads them instead of rendering them; they still display in an `<img>` tag.

### Other changes

- Uploading from a path stores the file name with its extension: `report.txt`, not `report`.
- The `thumbnail` option is ignored for SVG files.
- Exceptions are `InvalidArgumentException`, `LogicException` and `RuntimeException`. They extend `Exception`, so existing `catch (\Exception $e)` blocks still work.
- Stored paths always use `/`, also on Windows.
