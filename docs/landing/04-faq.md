---
view: components.home.faq
badge: FAQ
title: "{package.name}"
highlight: Questions
subtitle: "Quick answers about installing and using {package.name}."
---

## What is {package.name}?

{card.description} It's a free, open-source {technology.name} package by Pharaonic.

## How do I install {package.name}?

Run `composer require {package.composer}` in your project's root directory, then `php artisan migrate` to create the `uploads` table.

## What does {package.name} require?

The latest release requires {package.requiresText}. Thumbnails also need the GD or Imagick extension.

## Does it work with Amazon S3?

Yes. Upload to any S3 disk, set each file's visibility, and private files get temporary URLs that expire after the minutes you configure.

## Can anyone open a file on a local disk?

Local files are served by the package route to anyone holding the URL. The URL contains a random 67-character hash, so it cannot be guessed, but treat it like a share link. Use a private S3 disk for files that need expiring access.

## Is {package.name} free to use?

Yes. {package.name} is open source under the {package.license} license, so you can use it in personal and commercial projects.

## Where can I find the {package.name} documentation?

Read the [{package.name} documentation]({package.docsUrl}) for setup, configuration, and usage examples.

## How do I report a bug or contribute to {package.name}?

Open an issue or a pull request on [GitHub]({package.githubUrl}), or ask in the [Pharaonic Discord](https://discord.gg/XQG9RhvEvf).
