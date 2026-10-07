---
paths:
  - 'modules/MoonOrbit/Console/**'
---

# Console

## media:sync recursivo + --check de huérfanas
`media:sync` usa `Storage::disk()->allFiles()` (recursivo, no `files()`), envuelve cada registro en try/catch y devuelve FAILURE si alguno falla. `media:sync --check` lista las filas de media cuyo archivo falta en disco y devuelve 1 si hay huérfanas (fallo en CI). Cada archivo se registra con mimeType()/size() en el mismo try/catch.

## Retención de activity_logs: orbit:activity:prune + clearAll del resource
La limpieza de `activity_logs` va por dos vías: (1) comando `orbit:activity:prune {--days=90}` que borra filas con `created_at` anterior a `now()->subDays($days)`, registrado en el provider con `Schedule::command('orbit:activity:prune')->daily()`; (2) `ActivityLogResource::clearAll()` (async method) + botón rojo `topRightButtons` en ActivityLogIndexPage, que borra TODO con confirmación modal. `clearAll()` exige `$this->can(Ability::MASS_DELETE)` y devuelve JsonResponse 403 en vez de abort() (MethodController lo convierte a 500). Pruna respeta id de rol Super Admin.
