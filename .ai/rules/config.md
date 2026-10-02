---
paths:
  - config/filesystems.php
---

# Config

## Laravel 13: el disco `local` con serve=true secuestra /storage y devuelve 403
El skeleton de Laravel 13 pone `'serve' => true` en el disco `local` (root storage/app/private, sin `url`), así que FilesystemServiceProvider le registra `GET /storage/{path}`. Como `local` no define `visibility`, ServeFile::hasValidSignature() lo trata como privado y devuelve **403 en local / 404 en producción**. El disco `public` NO tiene `serve`, así que `Storage::disk('public')->url()` produce una URL que solo funciona si existe el symlink `public/storage`. Diagnosticar: si `/storage/x.png` da 403 y `/nope.png` da 404, es esto. Fix: `php artisan storage:link` (crea el symlink, el router de `artisan serve` sirve el archivo antes de llegar a Laravel). NO usar `storage:link --relative` sin `symfony/filesystem` instalado.
