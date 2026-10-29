## Thumbnails

Add a `thumbnail` option to store a resized copy of an image next to it. The thumbnail is an `Upload` of its own, linked through the `thumbnail` relation.

```php
$upload = upload($request->file('photo'), [
    'thumbnail' => ['width' => 300, 'ratio' => true],
]);

$upload->thumbnail->url;  // URL of the 300 px wide copy
$upload->thumbnail->name; // "photo-thumbnail.jpg" for photo.jpg
```

### Sizing

| Option | Description |
| --- | --- |
| `width` | Width in pixels. |
| `height` | Height in pixels. |
| `ratio` | `true` keeps the aspect ratio. Default `false`. |

- With `ratio`, the thumbnail is scaled to `width` when you give one, otherwise to `height`.
- Without `ratio`, the image is resized to exactly `width` × `height`. A dimension you leave out keeps its original size, so give both.

```php
// 400 × 200 photo
['width' => 100, 'ratio' => true];   // 100 × 50
['height' => 100, 'ratio' => true];  // 200 × 100
['width' => 50, 'height' => 50];     // 50 × 50
```

A `thumbnail` option without `width` or `height` throws `InvalidArgumentException` before anything is stored.

### Which files get one

Thumbnails are made for files with an `image/*` MIME type other than SVG. For any other file, including SVG, the option is ignored and the file is stored without a thumbnail. The image driver must be able to read the format: GD reads JPEG, PNG, GIF, WebP and BMP, and Imagick reads more.

The thumbnail keeps the original's format. Its name is the original name with `-thumbnail` before its own `extension` (`photo.png` gives `photo-thumbnail.png`), and it uses the same `disk`, `path` and `visibility` options as the original. Deleting the original deletes it too.

:::info Image driver
Thumbnails are generated with Intervention Image 2, using GD by default. To use Imagick, publish Intervention's configuration with `php artisan vendor:publish --provider="Intervention\Image\ImageServiceProviderLaravelRecent"` and set `driver` to `imagick`. Laravel 13's own image component (`config/images.php`) does not change how the package makes thumbnails.
:::
