---
name: Laravel Uploader

action:
  label: View on Packagist
  href: "{package.packagistUrl}"

views: components.packages

breadcrumbs:
  - label: Home
    href: route:home
  - label: Packages
    href: route:packages.index
  - label: "{technology.name} Packages"
    href: "url:/packages/{technology.slug}"
  - label: "{package.name}"

card:
  topic: media
  icon: folder
  tags: upload uploader file-upload files storage disk s3 thumbnail image visibility temporary-url signed-url eloquent
  description: Upload files to any Laravel disk with hashed names, image thumbnails, visibility control and secure URLs, through a facade, helpers and an Eloquent model.

seo:
  title: "{package.fullName} - File Uploads, Thumbnails and Secure URLs for Laravel"
  description: "{package.name} is a Laravel package that stores uploads on any disk with hashed names, image thumbnails, S3 visibility and temporary URLs, tracked by an Eloquent model. {package.downloadsShort}+ downloads, {package.license} licensed."
  keywords: laravel uploader, laravel file upload, laravel upload files, image thumbnail, laravel thumbnail, s3 upload, s3 visibility, temporary url, presigned url, private files, file storage, eloquent uploads
  author: Pharaonic
  images:
    - "{package.cover}"
  openGraph:
    type: website
    siteName: Pharaonic
  twitter:
    card: summary_large_image

schema:
  "@type": SoftwareSourceCode
  name: "{package.name}"
  description: "{package.name} is a Laravel package that stores uploads on any disk with hashed names, image thumbnails, S3 visibility and temporary URLs, tracked by an Eloquent model."
  image: "{package.cover}"
  codeRepository: "{package.githubUrl}"
  programmingLanguage: PHP
  runtimePlatform: "{technology.name}"
  version: "{package.version}"
  datePublished: "{package.publishedAt}"
  dateModified: "{package.updatedAt}"
  license: "https://opensource.org/licenses/{package.license}"
  isAccessibleForFree: true
  sameAs:
    - "{package.githubUrl}"
    - "{package.packagistUrl}"
  author:
    "@id": url:/#organization
  publisher:
    "@id": url:/#organization
  interactionStatistic:
    "@type": InteractionCounter
    interactionType: https://schema.org/DownloadAction
    userInteractionCount: "{package.downloads}"
---
