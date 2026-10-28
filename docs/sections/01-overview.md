:::badges
- Laravel Package {color=blue}
- {release.label} {color=green}
- {package.license} License {color=purple}
:::

# Laravel Uploader

Laravel Uploader stores uploaded files on any Laravel disk under a random hashed name and records each one in an `uploads` table. The `Upload` model gives you the right URL for the file, its readable size, an optional image thumbnail, and S3 visibility control, so a controller only needs one call to `upload()`.

:::features
### Any Disk {icon="folder"}
Store on local, public, S3 or any disk you configure, by default or per upload.

### Hashed Names {icon="lock"}
Each file gets a random 67-character hash that names it on the disk and in its URL.

### Thumbnails {icon="paper-clip"}
Resize images to a thumbnail by width, height, or both, with or without the aspect ratio.

### Visibility {icon="eye"}
Make an S3 file public or private and the stored object changes when you save the model.

### Smart URLs {icon="globe"}
Local files use the package route, private S3 files get temporary URLs, and the rest use the disk URL.

### Safe Serving {icon="shield-check"}
The file route sets no cookies, sends nosniff, and downloads types that could run script.
:::

:::info Quick Tip
Store the upload's hash on your own models, then load the file anywhere with uploaded($hash).
:::
