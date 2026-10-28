<p align="center"><a href="https://pharaonic.dev/packages/laravel/uploader" target="_blank"><img src="https://raw.githubusercontent.com/Pharaonic/laravel-uploader/develop/docs/cover.webp"></a></p>

<p align="center">
  <a href="https://php.net" target="_blank"><img src="https://img.shields.io/static/v1?label=PHP&message=8.0%20|%208.1%20|%208.2&color=blue&style=flat-square" alt="PHP Version : 8.0 | 8.1 | 8.2"></a>
  <a href="https://laravel.com" target="_blank"><img src="https://img.shields.io/static/v1?label=Laravel&message=9.x&color=F05340&style=flat-square" alt="Laravel Version : 9.x"></a>
  <img src="https://img.shields.io/static/v1?label=License&message=MIT&color=brightgreen&style=flat-square" alt="License">
  <img src="https://github.com/Pharaonic/laravel-uploader/actions/workflows/build.yml/badge.svg?branch=9.x" alt="Build">
  <br>
  <a href="https://packagist.org/packages/Pharaonic/laravel-uploader" target="_blank"><img src="https://img.shields.io/static/v1?label=Packagist&message=pharaonic/laravel-uploader&color=blue&logo=packagist&logoColor=white" alt="Source"></a>
  <a href="https://packagist.org/packages/pharaonic/laravel-uploader" target="_blank"><img src="https://poser.pugx.org/pharaonic/laravel-uploader/v" alt="Packagist Version"></a>
  <a href="https://packagist.org/packages/pharaonic/laravel-uploader" target="_blank"><img src="https://poser.pugx.org/pharaonic/laravel-uploader/downloads" alt="Packagist Downloads"></a>
</p>

<h3 align="center">Upload files to any Laravel disk with hashed names, image thumbnails, visibility control and secure URLs.</h3>
<br>

## Installation

```bash
composer require pharaonic/laravel-uploader
php artisan migrate
```

The service provider is registered automatically. Publish the configuration when you need to change it:

```bash
php artisan vendor:publish --tag=uploader-config
```

## Usage

```php
$upload = upload($request->file('avatar'), [
    'path' => 'avatars',
    'thumbnail' => ['width' => 200, 'ratio' => true],
]);

$upload->url;             // file URL, or a temporary URL for private S3 files
$upload->thumbnail->url;  // thumbnail URL
uploaded($upload->hash);  // find it again by hash
```

## Documentation

Full documentation: [Laravel Uploader Documentation](https://pharaonic.dev/packages/laravel/uploader).

## Support & Security

See [SUPPORT.md](SUPPORT.md) and [SECURITY.md](SECURITY.md). Contributions are welcome; see [CONTRIBUTING.md](CONTRIBUTING.md).

## License

MIT. See [LICENSE](LICENSE).
