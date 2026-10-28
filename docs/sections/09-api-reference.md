## API Reference

### Helpers

| Function | Description | Returns |
| --- | --- | --- |
| `upload(UploadedFile\|string $file, array $options = [])` | Store an uploaded file, or a file from a path, and save its record. | `Upload` |
| `uploaded(string $hash)` | Find an upload by hash; throws `ModelNotFoundException`. | `Upload` |

Both helpers are declared only when no function with the same name exists.

### Uploader facade

`Pharaonic\Laravel\Uploader\Facades\Uploader`, bound as `pharaonic.uploader`.

| Method | Description | Returns |
| --- | --- | --- |
| `upload(UploadedFile\|string $file, array $options = [])` | Same as the `upload()` helper. | `Upload` |
| `options()` | The resolved configuration options. | `array` |
| `route()` | The file route's URI and controller, as read from the configuration. | `Router` |

### Upload model

`Pharaonic\Laravel\Uploader\Models\Upload`, stored in the `uploads` table.

| Method | Description | Returns |
| --- | --- | --- |
| `url()` | URL for the file's disk and visibility. | `string` |
| `temporaryUrl(?int $expire = null)` | Temporary URL valid for `$expire` minutes, or the configured `expire`; throws `RuntimeException` on disks without temporary URLs. | `string` |
| `size(bool $decimal = true)` | Readable size such as "1.2 MB"; `false` uses binary units (KiB, MiB). | `string\|null` |
| `info(string $target = 'url', bool $isTemporary = false, ?int $expire = null)` | The URL for `url`, otherwise an array of file details. | `string\|array` |
| `visibility(string $visibility)` | Set the visibility; apply it with `save()`. Throws `LogicException` on local disks. | `static` |
| `public()` / `private()` | Shortcuts for `visibility('public')` and `visibility('private')`. | `static` |
| `thumbnail()` | Relation to the thumbnail upload. | `HasOne` |

| Property | Type | Description |
| --- | --- | --- |
| `id` | `int` | Primary key. |
| `hash` | `string` | Random 67-character identifier, used in URLs. |
| `name` | `string` | Original file name. |
| `path` | `string` | Path on the disk. |
| `size` | `int` | Size in bytes. |
| `extension` | `string` | Extension detected from the file contents. |
| `mime` | `string` | MIME type detected from the file contents. |
| `disk` | `string` | Disk name. |
| `visibility` | `string\|null` | `public` or `private`; `null` on local disks. |
| `thumbnail_id` | `int\|null` | ID of the thumbnail upload. |
| `thumbnail` | `Upload\|null` | The thumbnail upload. |
| `url` | `string` | Same as `url()`. |
| `temporary_url` | `string` | Same as `temporaryUrl()`. |
| `created_at` / `updated_at` | `Carbon` | Timestamps. |

### Concerns

The model's URL and visibility methods come from two traits you can reuse on a model with the same columns:

| Trait | Methods |
| --- | --- |
| `Pharaonic\Laravel\Uploader\Concerns\HasUrls` | `url()`, `temporaryUrl()`, `url` and `temporary_url` attributes |
| `Pharaonic\Laravel\Uploader\Concerns\HasVisibility` | `visibility()`, `public()`, `private()` |

### Publish tags

| Tag | Publishes |
| --- | --- |
| `uploader-config`, `pharaonic-config` | `config/pharaonic/uploader.php` |
| `uploader-migrations`, `pharaonic-migrations` | The `create_uploads_table` and `make_uploads_hash_unique` migrations |
| `laravel-uploader`, `pharaonic` | Both |
