---
paths:
  - 'modules/MoonLaunch/Traits/**'
---

# Traits

## Guardar permisos en métodos async (WithSoftDeletes)
Los métodos async (`restore`/`forceDelete`) reciben `CrudRequestContract::getResource(): ?CrudResourceContract`. Comprobar `$resource === null || ! $resource->can(Ability::RESTORE|FORCE_DELETE)` y devolver un JsonResponse 403 (JsonResponse::make()->setStatusCode(403)->toast(...)): MethodController convierte cualquier Throwable en 500, así que `abort(403)` no devuelve 403. NO usar `$this->getResource()` del page/trait: `Page::getResource()` es `?ResourceContract` (sin `can()`), hace falta `instanceof CrudResourceContract`. En `trashActions()` calcular `canAction()` una vez y pasarlo a `->canSee()`.

## El alias 'deleted' de la papelería es estable, el slug no
La query-tag de la papelera lleva `->alias('deleted')`: sin alias, `QueryTag::getUri()` devolvería `Str::slug($label)` (eliminados/trashed según locale) y el `canSee(query-tag !== 'deleted')` del mass delete dejaría de ocultarse. Nunca reconstruir la query-tag con `Str::slug(label)`: usar el alias fijo o `$tag->getUri()`.
