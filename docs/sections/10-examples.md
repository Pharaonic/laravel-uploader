## Examples

### 1. User avatars with a thumbnail

Keep the avatar's hash on the user and show the thumbnail in lists.

- ===Migration

  ```php title="database/migrations/2026_10_28_000000_add_avatar_to_users_table.php"
  Schema::table('users', function (Blueprint $table) {
      $table->string('avatar', 350)->nullable();
  });
  ```

- ===Controller

  ```php title="app/Http/Controllers/AvatarController.php"
  public function update(Request $request)
  {
      $request->validate(['avatar' => 'required|image|max:2048']);

      $user = $request->user();

      if ($user->avatar) {
          uploaded($user->avatar)->delete();
      }

      $upload = upload($request->file('avatar'), [
          'path' => 'avatars',
          'thumbnail' => ['width' => 96, 'ratio' => true],
      ]);

      $user->update(['avatar' => $upload->hash]);

      return back();
  }
  ```

- ===Model

  ```php title="app/Models/User.php"
  use Pharaonic\Laravel\Uploader\Models\Upload;

  public function avatarUpload()
  {
      return $this->belongsTo(Upload::class, 'avatar', 'hash');
  }
  ```

- ===Blade View

  ```blade title="resources/views/users/index.blade.php"
  @foreach ($users as $user)
      <img src="{{ $user->avatarUpload->thumbnail->url ?? asset('img/avatar.png') }}" alt="{{ $user->name }}">
  @endforeach
  ```

### 2. Private invoices on S3

Store invoices privately and hand out links that expire after five minutes.

```php title="app/Http/Controllers/InvoiceController.php"
public function store(Request $request, Order $order)
{
    $request->validate(['invoice' => 'required|mimes:pdf|max:5120']);

    $upload = upload($request->file('invoice'), [
        'disk' => 's3',
        'path' => 'invoices',
        'visibility' => 'private',
    ]);

    $order->update(['invoice' => $upload->hash]);
}

public function show(Order $order)
{
    $this->authorize('view', $order);

    return redirect(uploaded($order->invoice)->temporaryUrl(5));
}
```

### 3. Publish a draft image

Uploads start private on S3; make an image public when the post goes live.

```php
$post->update(['published_at' => now()]);

uploaded($post->cover)->public()->save();
```

### 4. Return file details from an API

```php title="routes/api.php"
Route::get('/files/{hash}', function (string $hash) {
    return uploaded($hash)->load('thumbnail')->info('details');
});
```

### 5. Import files generated on the server

```php title="app/Console/Commands/ArchiveReports.php"
foreach (glob(storage_path('app/reports/*.csv')) as $path) {
    upload($path, ['path' => 'archive/reports']);

    unlink($path);
}
```
