## Installation

Install the package with Composer. Laravel discovers the service provider automatically, and the migration runs with your own.

### Requirements

- PHP 8.0
- Laravel 7.30 or newer within 7.x
- `pharaonic/laravel-readable` 7.x and `intervention/image` 2.7+ (installed automatically)
- The GD or Imagick extension, only to generate thumbnails
- `league/flysystem-aws-s3-v3`, only to store files on S3

### Composer Installation

```bash title="Terminal" no-line-numbers
composer require pharaonic/laravel-uploader
```

### Run Migrations

The package loads its migration automatically, so `migrate` creates the `uploads` table.

```bash title="Terminal" no-line-numbers
php artisan migrate
```

To copy the migration into `database/migrations` instead, publish it:

```bash title="Terminal" no-line-numbers
php artisan vendor:publish --tag=uploader-migrations
```

### Publish Configuration

Publish the configuration file to `config/pharaonic/uploader.php` when you need to change a default:

```bash title="Terminal" no-line-numbers
php artisan vendor:publish --tag=uploader-config
```

The `pharaonic` and `laravel-uploader` tags publish both the configuration and the migration.

:::success Installation Complete
You're all set! Try `upload($request->file('document'))->url` in a controller.
:::
