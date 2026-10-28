# Maintaining This Package

This file records package-specific maintainer facts that are safe to keep in the public repository.

## Purpose

Upload files to any Laravel disk with hashed names, optional image thumbnails, S3 visibility control, temporary URLs, and a route that streams files from local disks.

## Supported Laravel lines

One branch per Laravel major, from `6.x` to the newest major. Each line's PHP range, `illuminate/*` constraint, and tools follow the Pharaonic Laravel line table. `6.x` runs PHPUnit; later lines run Pest.

Releases 1.x–3.x supported Laravel 6+, and 4.x supported Laravel 9+. The `N.x` lines replace them; 4.x receives no further releases.

## Core library

None. File sizes are formatted by `pharaonic/laravel-readable` (`readable_file_size()`), required at the same Laravel line. Thumbnails use `intervention/image` 2.x, kept on every line because 3.x needs PHP 8.1 and drops the Laravel facade.

## Public API invariants

Configuration keys (`pharaonic.uploader.*`), publish tags, and registered Blade, Artisan, macro, and validation names are public API.

- Global helpers `upload()` and `uploaded()`, declared only when a function with the same name does not exist.
- The `Uploader` facade (`options()`, `route()`, `upload()`), bound as `pharaonic.uploader`.
- The `Upload` model, its `uploads` table, its `thumbnail` relation, and its `url()`, `temporaryUrl()`, `info()`, `size()`, `visibility()`, `public()` and `private()` methods.
- Upload options: `disk`, `path`, `visibility`, `thumbnail` (`width`, `height`, `ratio`).
- The route name `uploader.file` and URI `{uri}/{hash}` (default `/file/{hash}`). Stored content links to these URLs; never change them on an existing line.
- The migration file `2021_02_01_000001_create_uploads_table.php` keeps its name. Its class stays named while `6.x`–`8.x` exist.
- Publish tags: `pharaonic`, `laravel-uploader`, `pharaonic-config`, `uploader-config`, `pharaonic-migrations`, `uploader-migrations`.
- Model traits live in `Concerns` (`HasUrls`, `HasVisibility`). The 4.x `Traits\*` names were removed in 6.0.0.
- The deprecated `Pharaonic.uploader` key is applied from `config/Pharaonic/uploader.php` and kept in sync with `pharaonic.uploader`. It is removed only in the first line that supports PHP 8.6.

## Package-specific constraints

- The file route has no middleware group: it must set no session or cookies. It sends `X-Content-Type-Options: nosniff` and serves only the types listed in `Support\Disposition` inline; everything else is an attachment.
- Files on local disks are reachable by anyone holding the URL; the hash (67 random characters) is the only secret.
- `providesTemporaryUrls()` exists from Laravel 9; older lines treat an `s3` driver as supporting temporary URLs.
- Thumbnail tests need ext-gd and are skipped without it; CI installs it.

## Branch notes

Fixes land on the oldest affected line and are ported to every later line and `develop`.

## Documentation

https://pharaonic.dev/packages/laravel/uploader

Keep this file factual and package-specific. General Pharaonic engineering policy belongs in the private engineering repository, not here.
