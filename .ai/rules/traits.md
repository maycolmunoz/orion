---
paths:
  - 'modules/MoonLaunch/Traits/**'
---

# Traits

## `WithSoftDeletes` async methods get the resource from the request
`restore`/`forceDelete` receive `CrudRequestContract::getResource(): ?CrudResourceContract`. Check `$resource === null || ! $resource->can(Ability::RESTORE | Ability::FORCE_DELETE)` and return `JsonResponse::make()->setStatusCode(403)->toast(...)` — `abort(403)` becomes a 500 (see `moon-shine.md`). Do **not** use `$this->getResource()`: `Page::getResource()` is `?ResourceContract` and has no `can()`, so an `instanceof CrudResourceContract` check is required.

## The trash query-tag alias is stable; the slug is not
The trash query-tag carries `->alias('deleted')`. Without it `QueryTag::getUri()` would return `Str::slug($label)` (locale-dependent: *eliminados*/*trashed*), and the mass-delete `canSee(query-tag !== 'deleted')` guard would stop hiding itself. Never rebuild the tag with `Str::slug(label)` — use the fixed alias or `$tag->getUri()`.