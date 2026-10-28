## Configuration

Publish the file with `php artisan vendor:publish --tag=uploader-config` and edit `config/pharaonic/uploader.php`. Every option has a default, so you only need the ones you change.

```php title="config/pharaonic/uploader.php"
return [
    'disk' => env('FILESYSTEM_DISK', env('FILESYSTEM_DRIVER', 'local')),
    'path' => '/',
    'expire' => 30,
    'visibility' => 'private',
    'extensional' => true,
    'controller' => \Pharaonic\Laravel\Uploader\Http\Controllers\UploadController::class,
    'uri' => '/file',
];
```

| Option | Default | Description |
| --- | --- | --- |
| `disk` | `FILESYSTEM_DISK`, then `FILESYSTEM_DRIVER`, then `local` | Disk used when an upload has no `disk` option. |
| `path` | `/` | Base directory for every upload on the disk. |
| `expire` | `30` | Lifetime of temporary URLs, in minutes. |
| `visibility` | `private` | Visibility of new files on disks that are not local (S3). |
| `extensional` | `true` | Add the file extension to local file URLs (`/file/{hash}.pdf`). |
| `controller` | `UploadController::class` | Invokable controller of the file route. |
| `uri` | `/file` | URI prefix of the file route; the hash follows it. |

### Where files are stored

Each file is stored at the base `path`, then the upload's own `path` option, then the upload date, then the hash and extension:

```text no-copy no-line-numbers
uploads/avatars/2026-10-28/{hash}.jpg
```

:::warning Route changes
`uri` and `controller` are read when the routes load. If you change them after caching routes, run `php artisan route:clear`. Changing `uri` changes the URL of every local file already shared.
:::
