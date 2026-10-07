---
paths:
  - 'modules/MoonOrbit/MoonShine/Pages/**'
---

# Moon Shine Pages

## File Manager: disco public/ dir files, sin tabla y ruta saneada
FileManagerPage (MoonOrbit, Super Admin only como SettingsPage) trabaja directo con Storage::disk('public') en 'files/', plano y sin modelo ni migración: la lista sale de $disk->files('files') y las subidas de un File field multiple (se guardan en uploadFiles con ->store). No añadir tabla de metadatos salvo necesidad real. deleteFile recibe 'path' del cliente, así que isManagedPath() exige no vacío, sin '..', prefijo 'files/' y exists antes de borrar (evita path traversal); los métodos async devuelven JsonResponse 403/422 con toast, nunca abort().

## hidden_logo: solo el logo almacenado, nunca ruta arbitraria
`hidden_logo` es el helper oculto del campo Image de MoonShine y viene del navegador: NUNCA persistirlo como setting y siempre acotarlo a lo ya almacenado. En `SettingsPage::saveSettings` se lee `$previousLogo = Setting::get('logo')` ANTES del `validate()` y `hidden_logo` lleva `Rule::in(array_filter([$previousLogo]))`. Sin esa restricción un Super Admin podría inyectar una ruta arbitraria que luego `Storage::delete($previousLogo)` borraría del disco public.

## El file manager pagina con paginate(24) + Paginator
FileManagerPage pagina con `media()` → `LengthAwarePaginator` vía `paginate(24)->withQueryString()` y se renderiza `Paginator::make($media)` justo debajo del `CardsBuilder` (`grid($media->items())`, que acepta `iterable`). Se mantiene la autorización por rol Super Admin (page, no resource). En tests, `latest()` empata con timestamps de segundo: si el test compara páginas, forzar `created_at` distintos.
