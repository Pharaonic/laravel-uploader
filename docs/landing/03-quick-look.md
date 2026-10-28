---
view: components.packages.quick-look
title: A quick look
subtitle: Store an avatar with a thumbnail and keep its hash on the user.
file: app/Http/Controllers/AvatarController.php
language: php
code: |
  public function update(Request $request)
  {
      $upload = upload($request->file('avatar'), [
          'path' => 'avatars',
          'thumbnail' => ['width' => 200, 'ratio' => true],
      ]);

      $request->user()->update(['avatar' => $upload->hash]);

      return ['url' => $upload->url, 'thumbnail' => $upload->thumbnail->url];
  }
---
