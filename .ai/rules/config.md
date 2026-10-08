---
paths:
  - 'config/filesystems.php'
---

# Config

## Laravel 13's `local` disk hijacks `/storage` and returns 403
The Laravel 13 skeleton sets `'serve' => true` on the `local` disk (root `storage/app/private`, no `url`), so `FilesystemServiceProvider` registers `GET /storage/{path}`. Because `local` defines no `visibility`, `ServeFile::hasValidSignature()` treats it as private and answers **403 locally / 404 in production**. The `public` disk has no `serve`, so `Storage::disk('public')->url()` only works when the `public/storage` symlink exists. Diagnose with the split: `/storage/x.png` → 403 while `/nope.png` → 404. Fix is `php artisan storage:link`. Do not use `storage:link --relative` without `symfony/filesystem` installed.