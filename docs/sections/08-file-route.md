## The File Route

Files on the disk named `local` are not reachable from the web, so the package registers a route that streams them:

```text no-copy no-line-numbers
GET /file/{hash}   name: uploader.file
```

The extension in the URL is optional when `extensional` is on: `/file/{hash}.pdf` and `/file/{hash}` serve the same file. An unknown hash, or a file missing from the disk, returns 404.

```php
route('uploader.file', $upload->hash); // "https://example.com/file/Xb3..."
```

### Response headers

| Header | Value |
| --- | --- |
| `Content-Type` | The MIME type detected when the file was uploaded. |
| `Content-Disposition` | `inline` for images, audio, video, PDF and plain text; `attachment` for everything else, including SVG and HTML. |
| `X-Content-Type-Options` | `nosniff` |

The route runs no middleware group, so it starts no session and sets no cookies.

:::warning Access
Anyone holding a local file's URL can open it. The hash has 67 random characters, so the URL cannot be guessed, but it works like a share link. For files that need access control, use a private S3 disk with temporary URLs, or a controller of your own.
:::

### Your own controller

Point the `controller` option at an invokable controller to change how files are served. Middleware registered in its constructor runs on the route:

```php title="app/Http/Controllers/ProtectedFileController.php"
namespace App\Http\Controllers;

use Pharaonic\Laravel\Uploader\Http\Controllers\UploadController;

class ProtectedFileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['web', 'auth']);
    }

    public function __invoke(string $hash)
    {
        return (new UploadController)($hash);
    }
}
```

```php title="config/pharaonic/uploader.php"
'controller' => \App\Http\Controllers\ProtectedFileController::class,
```
