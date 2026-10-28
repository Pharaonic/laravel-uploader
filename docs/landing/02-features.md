---
view: components.packages.features
variant: compact
badge: Key Features
title: Everything you need to handle uploads
subtitle: Pass a file to `upload()` and get back a model that knows its URL, size and thumbnail.
items:
  - icon: folder
    title: Any Disk
    text: Store on `local`, `public`, `s3` or any disk from `config/filesystems.php`, per upload or by default.
  - icon: lock
    title: Hashed File Names
    text: Every file gets a random 67-character hash, so its name and URL cannot be guessed.
  - icon: paper-clip
    title: Image Thumbnails
    text: Generate a resized thumbnail with a width, a height, or both, keeping the aspect ratio when you ask.
  - icon: eye
    title: Visibility Control
    text: Switch an S3 file between `public()` and `private()` and the stored object follows on `save()`.
  - icon: globe
    title: The Right URL
    text: "`$upload->url` returns a route for local files, a temporary URL for private S3 files, and the disk URL otherwise."
  - icon: shield-check
    title: Safe File Route
    text: Local files stream without sessions or cookies, with `nosniff`, and risky types are sent as downloads.
---
