# Changelog

All notable changes to this project will be documented in this file.

## 13.0.0 - Unreleased

`13.x` supports Laravel 13 on PHP 8.3, 8.4 and 8.5.

### Compatibility

- Laravel 13.0+ on PHP 8.3, 8.4 or 8.5. PHP 8.2 is no longer supported on this line.
- Requires `pharaonic/laravel-readable` `^13.0`.
- The same API, configuration, route, migrations and behavior as 12.0.0, including the deprecated `Pharaonic.uploader` key.
- Thumbnails still use Intervention Image 2 and its `image` configuration. Laravel 13 binds `image` to its own image manager, so the package creates the Intervention manager itself instead of using the `Image` facade.

## 12.0.0 - Unreleased

`12.x` supports Laravel 12 on PHP 8.2, 8.3, 8.4 and 8.5.

### Compatibility

- Laravel 12.0+ on PHP 8.2, 8.3, 8.4 or 8.5.
- Requires `pharaonic/laravel-readable` `^12.0`.
- The same API, configuration, route, migrations and behavior as 11.0.0, including the deprecated `Pharaonic.uploader` key.
- `temporaryUrl()` on a `local` disk that Laravel serves (`'serve' => true`, the Laravel 12 default) returns the framework's signed URL instead of throwing; `url()` still uses the package file route for the `local` disk.

## 11.0.0 - Unreleased

`11.x` supports Laravel 11 on PHP 8.2, 8.3 and 8.4.

### Compatibility

- Laravel 11.0+ on PHP 8.2, 8.3 or 8.4. PHP 8.1 is no longer supported on this line.
- Requires `pharaonic/laravel-readable` `^11.0`.
- The same API, configuration, route, migrations and behavior as 10.0.0, including the deprecated `Pharaonic.uploader` key.

## 10.0.0 - Unreleased

`10.x` supports Laravel 10 on PHP 8.1, 8.2 and 8.3.

### Compatibility

- Laravel 10.0+ on PHP 8.1, 8.2 or 8.3. PHP 8.0 is no longer supported on this line.
- Requires `pharaonic/laravel-readable` `^10.0`.
- The same API, configuration, route, migrations and behavior as 9.0.0, including the deprecated `Pharaonic.uploader` key.

## 9.0.0 - 2026-10-28

`9.x` supports Laravel 9 on PHP 8.0, 8.1 and 8.2.

### Compatibility

- Laravel 9.33+ on PHP 8.0, 8.1 or 8.2. `pharaonic/laravel-readable` 9.x needs Laravel 9.33, the first 9.x release that requires a Carbon release working on PHP 8.2.
- Requires `pharaonic/laravel-readable` `^9.0`.
- The same API, configuration, route, migrations and behavior as 8.0.0, including the deprecated `Pharaonic.uploader` key.

## 8.0.0 - 2026-10-28

`8.x` supports Laravel 8 on PHP 8.0 and 8.1.

### Compatibility

- Laravel 8.75+ on PHP 8.0 or 8.1.
- Requires `pharaonic/laravel-readable` `^8.0`.
- The same API, configuration, route, migrations and behavior as 7.0.0, including the deprecated `Pharaonic.uploader` key.

## 7.0.0 - 2026-10-28

`7.x` supports Laravel 7 on PHP 8.0.

### Compatibility

- Laravel 7.30+ on PHP 8.0.
- Requires `pharaonic/laravel-readable` `^7.0`.
- The same API, configuration, route, migrations and behavior as 6.0.0, including the deprecated `Pharaonic.uploader` key.

## 6.1.0 - 2026-10-28

### Changed

- Behavior change from 6.0.x: a thumbnail's `name` puts `-thumbnail` before the extension and ends with the thumbnail's own extension (`photo-thumbnail.jpg` instead of `photo.jpg-thumbnail`), so the file route's `Content-Disposition` filename keeps a usable extension. Thumbnails stored before keep their name.

## 6.0.0 - 2026-10-28

The first release of the per-Laravel-major lines. `6.x` supports Laravel 6 on PHP 8.0.

### Added

- Support for Laravel 6, 7 and 8 again, on PHP 8.0, through the `6.x`, `7.x` and `8.x` lines.
- Publish tags per resource: `uploader-config`, `pharaonic-config`, `uploader-migrations` and `pharaonic-migrations`. `pharaonic` and `laravel-uploader` still publish both.
- `X-Content-Type-Options: nosniff` and a `Content-Disposition` header on files served by the file route.
- Feature and unit test suites, PHPStan, Laravel Pint, GitHub community files, Dependabot and a CI matrix.

### Changed

- The file route serves types a browser can run script from (SVG, HTML, XML, JavaScript, and any type not known to be safe) as `attachment`. Images, audio, video, PDF and plain text stay `inline`.
- The file route no longer runs the `web` middleware group, so it starts no session and sets no cookies. Its name (`uploader.file`) and URI (`/file/{hash}`) are unchanged.
- The configuration is merged under `pharaonic.uploader` and published to `config/pharaonic/uploader.php`.
- A thumbnail is generated only for raster images; the `thumbnail` option is ignored for SVG files.
- Temporary thumbnail files are written to the system temporary directory instead of `storage/app/public/pharaonic-thumbs`.
- `upload()` with a file path stores the file name with its extension (`report.txt`, not `report`).
- The `disk` option defaults to `FILESYSTEM_DISK`, then `FILESYSTEM_DRIVER` (Laravel 6–8), then `local`.
- The package requires the `illuminate/*` components it uses instead of `laravel/framework`, and `pharaonic/laravel-readable` `^6.0`.
- The migration class is named (`CreateUploadsTable`) so it runs on Laravel 6–8. Its file name and schema are unchanged.
- A new migration makes `uploads.hash` unique instead of only indexed. Run `php artisan migrate`; it fails only if the table already holds duplicate hashes.
- Exceptions are SPL subclasses of `Exception`: `InvalidArgumentException` for a missing path or thumbnail size, `LogicException` for visibility on a local disk, `RuntimeException` for a disk without temporary URLs. Existing `catch (\Exception)` blocks still catch them.

### Deprecated

- The `Pharaonic.uploader` configuration key and `config/Pharaonic/uploader.php`. A file published there by 4.x is still applied, and `config('Pharaonic.uploader')` still returns the options. `config/pharaonic/uploader.php` takes precedence when both exist.

### Removed

- `Pharaonic\Laravel\Uploader\Traits\Urls` and `Traits\Visibility`. The `Upload` model uses `Concerns\HasUrls` and `Concerns\HasVisibility` instead; a model of your own that used the old traits must switch to the new names.

### Fixed

- Stored paths use `/` on Windows, so S3 keys and saved paths no longer contain backslashes.
- A thumbnail without a width or height is rejected before the original file is stored.
- When the image driver cannot read an image, the original file is deleted from the disk instead of being left there without a record.
- A file deleted from the disk returns 404 from the file route instead of an empty response.
- The `Content-Disposition` file name of temporary URLs is escaped.
- `temporaryUrl()` declares its nullable `$expire` explicitly, so PHP 8.4 reports no deprecation.
