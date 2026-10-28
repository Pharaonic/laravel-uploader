## Upgrading from 4.x

The `7.x` line runs on Laravel 7 with PHP 8.0. Most applications upgrade without code changes; check the points below.

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
