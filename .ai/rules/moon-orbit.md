---
paths:
  - 'modules/MoonOrbit/**'
---

# MoonOrbit

Optional module. Ships DISABLED — the provider is commented out in `bootstrap/providers.php`. Enabling it is uncommenting that line, not fixing a bug. See `tests.md` for the conditional test guard that keeps the suite green while it is off.

## Media is the source of truth, not the disk
The `media` table (disk `public`, dir `files`) replaced the old disk scan of `FileManagerPage`. The list comes from `Media::query()` (`onlyTrashed()` for the trash), uploads create rows, deletes are soft. `deleteMedia`/`restoreMedia`/`forceDeleteMedia` work by id, not by path, so there is no path to sanitize; `forceDelete` also removes the file with `Storage::disk($media->disk)->delete($media->path)`. `Media` is observed, so uploads/deletes/restores land in the activity log. `MediaField` extends `Select` (no Blade of its own) and stores the Media **id**.

## `ActivityObserver`: skip `deleted` while force-deleting
`forceDelete()` fires `deleted` (with `isForceDeleting()` true) and then `forceDeleted`; the observer ignores the first and logs the second, so a force delete is not double-counted. `restored`/`forceDeleted` never fire on models without `SoftDeletes`, so no `registerModelEvent` is needed. `attributes()` excludes `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at` — the timestamps are dropped so a restore does not pollute the log.

## `media:sync` is recursive and fails loudly
`media:sync` uses `Storage::disk()->allFiles()` (recursive, not `files()`), wraps each row in try/catch recording `mimeType()` and `size()`, and returns `FAILURE` if any file fails. `media:sync --check` lists rows whose file is missing on disk and returns 1 when it finds orphans, so it fails the build. Both are idempotent.

## Activity log retention
Two paths: `orbit:activity:prune {--days=90}` deletes rows older than `now()->subDays($days)`, scheduled as `Schedule::command('orbit:activity:prune')->daily()`; and `ActivityLogResource::clearAll()` (async) behind the red `topRightButtons` on `ActivityLogIndexPage`, with a confirmation modal that wipes everything. `clearAll()` requires `$this->can(Ability::MASS_DELETE)` and returns a 403 `JsonResponse` (not `abort()`, which `MethodController` would turn into 500). Prune respects the Super Admin role id.

## Panel appearance is wired with `$config->set()` and closures
`title`, `logo`, `logo_small` and `palette` are set in `MoonOrbitServiceProvider::boot()` as closures — `ConfiguratorContract::get()` resolves them via `value()`, so the DB is read at render, not at boot. `AbstractLayout::colors()` re-reads the palette every render, so the settings page only needs to persist the class-string (allowlist in `SettingsPage::palettes()`). `logo_small` must always be set together with `logo` or MoonShine shows its own logo on mobile. The `MoonShineLayout` footer reads `moonshineConfig()->getTitle()`, so renaming the app updates it.

## Layout switch is a setting, not a subclass
The selector persists `sidebar`/`topbar` (allowlist in `SettingsPage::layouts()`) and the provider sets `layout_mode` with a `sidebar` fallback. `MoonShineLayout::build()` flips `$topBar`/`$sidebar` from that key; the class stays final — do not subclass it. `Login` and `ErrorPage` carry their own `#[Layout]` and are unaffected. Saving returns `JsonResponse::redirect()` to the same page so the palette/layout apply and the preview refreshes.

## Subject reads as "Model · Record"
`ActivityLogResource::subjectPreview()` and `modelName()` render the subject as `Model · Record` via the translated map `moon-orbit::ui.activity_log.models.{User,Role,Setting}`, falling back to `class_basename`. The `subject_type` filter reuses `modelName()`; never show a bare `subject_label` or a raw `class_basename`.