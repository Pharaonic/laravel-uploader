## Basic Usage

Pass an uploaded file to the `upload()` helper. It stores the file and returns the saved `Upload` model.

```php title="app/Http/Controllers/DocumentController.php"
use Illuminate\Http\Request;

public function store(Request $request)
{
    $request->validate(['document' => 'required|file|max:10240']);

    $upload = upload($request->file('document'));

    return [
        'hash' => $upload->hash,   // "Xb3...": 67 random characters
        'url' => $upload->url,     // "https://example.com/file/Xb3....pdf"
        'size' => $upload->size(), // "1.2 MB"
    ];
}
```

The `Uploader` facade does the same:

```php
use Pharaonic\Laravel\Uploader\Facades\Uploader;

$upload = Uploader::upload($request->file('document'));
```

:::warning Validate first
The package stores whatever you pass it. Validate the size and type of every upload with Laravel's validation rules before calling `upload()`.
:::

### Upload options

The second argument overrides the defaults for one upload:

```php
$upload = upload($request->file('invoice'), [
    'disk' => 's3',            // any disk from config/filesystems.php
    'path' => 'invoices/2026', // added under the configured base path
    'visibility' => 'public',  // S3 and other non-local disks only
]);
```

| Option | Type | Description |
| --- | --- | --- |
| `disk` | `string` | Disk to store the file on. Defaults to the `disk` option in the configuration. |
| `path` | `string` | Directory under the configured base path. |
| `visibility` | `string` | `public` or `private`. Ignored on disks that use the local driver. |
| `thumbnail` | `array` | `width`, `height` and `ratio` of a thumbnail. See [Thumbnails](#thumbnails). |

### Upload a file from a path

`upload()` also accepts the path of a file on the server. The original file stays where it is.

```php
$upload = upload(storage_path('app/exports/report.csv'));

$upload->name; // "report.csv"
```

A path that does not exist throws `InvalidArgumentException`.

### Find an upload again

Store the hash on your own models, then load the upload with `uploaded()`. It throws `ModelNotFoundException`, which Laravel turns into a 404, when no upload has that hash.

```php
$upload = uploaded($user->avatar);
```

The `Upload` model is a regular Eloquent model, so you can also query it:

```php
use Pharaonic\Laravel\Uploader\Models\Upload;

$images = Upload::where('mime', 'like', 'image/%')->latest()->get();
```

### Delete an upload

Deleting the model deletes the stored file and its thumbnail.

```php
uploaded($hash)->delete();
```
