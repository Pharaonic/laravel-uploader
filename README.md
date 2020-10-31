<p align="center"><a href="https://pharaonic.io" target="_blank"><img src="https://raw.githubusercontent.com/Pharaonic/logos/main/uploader.jpg" width="470"></a></p>

<p align="center">
<a href="https://github.com/Pharaonic/laravel-uploader" target="_blank"><img src="http://img.shields.io/badge/source-pharaonic/laravel--uploader-blue.svg?style=flat-square" alt="Source"></a> <a href="https://packagist.org/packages/pharaonic/laravel-uploader" target="_blank"><img src="https://img.shields.io/packagist/v/pharaonic/laravel-uploader?style=flat-square" alt="Packagist Version"></a><br>
<a href="https://laravel.com" target="_blank"><img src="https://img.shields.io/badge/Laravel->=6.0-red.svg?style=flat-square" alt="Laravel"></a> <img src="https://img.shields.io/packagist/dt/pharaonic/laravel-uploader?style=flat-square" alt="Packagist Downloads"> <img src="http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Source">
</p>


##### Laravel Uploader provides a quick and easy methods to upload files and handling visibility with simple routing.

###### 



## Install

Install the latest version using [Composer](https://getcomposer.org/):

```bash
$ composer require pharaonic/laravel-uploader
```

then publish the migration & config files
```bash
$ php artisan vendor:publish --tag=laravel-uploader
$ php artisan migrate
```



## Usage
- [Configurations / Options](#config)
- [Uploading & Getting](#UG)
- [URL & Uploader](#URL_UP)
- [Deleting](#dd)
- [Permits (Private Files)](#permits)



<a name="config"></a>

#### Configurations / Options
```php
/**
*	prefix 		=> Hash Prefix
*	visitable	=> Visits Counter
*	private		=> Only Permitted Users
*/
```



<a name="UG"></a>

#### Uploading & Getting
###### function upload(UploadedFile $file, array $options = [])
###### function getUpload(string $hash)

```php
// Upload File
$file = upload($request->image, [
	'visitable'	=> true
]);

// Getting Uploaded File with Hash code
$file = getUploaded('5e63885fa771d1.12185481920ncF3...');

// Information
echo $file->hash; // File's Hash
echo $file->name; // File's Name
echo $file->path; // File's Path
echo $file->size; // File's Size in Bytes
echo $file->readableSize(); // File's Readable Size [B, KB, MB, ...] (1000)
echo $file->readableSize(false); // File's Readable Size [B, KiB, MiB, ...] (1024)
echo $file->extension; // File's Extension
echo $file->mime; // File's MIME

echo $file->visits; // File's visits (Visitable File)
```



<a name="URL_UP"></a>

#### URL & Uploader

```php
echo $file->url; // Getting Uploaded File's URL
// <img src"{{ $file->url }}" alt="{{ $file->name }}">

$user = $file->uploader; // Getting Uploader's Model
```



<a name="dd"></a>

#### Deleting Uploaded File
```php
$file->delete();
```



<a name="permits"></a>

#### Permits (Private File)

```php
$permits = $file->permits; // Getting Permits List
$permitted = $file->isPermitted($user); // Checking if permitted (App\User)

$file->permit($user, '2021-02-01'); // Permitting a user
$file->forbid($user); // Forbidding a user
```




## License

[MIT license](LICENSE.md)
