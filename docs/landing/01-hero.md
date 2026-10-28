---
view: components.packages.package-hero
badges:
  - label: Laravel Package
    color: blue
  - label: "{package.latestVersionLabel}"
    color: green
  - label: "{package.license} License"
    color: purple
  - label: "{package.downloadsShort}+ downloads"
    color: blue
eyebrow: "{package.name}"
title: Upload once,
highlight: serve it right
buttons:
  - label: View Full Documentation
    href: "{card.docsUrl}"
    style: primary
    icon: arrow-right
  - label: View on GitHub
    href: "{package.githubUrl}"
    style: ghost
    external: true
install: "{card.install}"
labels:
  copy: Copy
  copied: Copied!
---

{package.name} stores every upload on the Laravel disk you choose, under an unguessable hashed name, and keeps a record of it in an `uploads` table. You get the right URL for each file, whether it lives on a local disk, a public disk, or a private S3 bucket, plus image thumbnails and visibility changes in one call.
