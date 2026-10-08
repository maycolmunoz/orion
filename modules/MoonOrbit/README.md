# MoonOrbit

Optional extras for Orion: appearance settings, a file manager backed by a media library, and an
audited activity log. **Ships disabled** — its provider is commented out in `bootstrap/providers.php`.
Depends on [MoonLaunch](../MoonLaunch/README.md).

## 📦 What you get

-   `settings-page` — app name, logo, palette, layout (sidebar / topbar). Super Admin only.
-   `file-manager-page` — uploads on the `public` disk under `files/`, with a soft-delete trash. Super Admin only.
-   `Media` + `MediaField` — a reusable picker that stores a media id.
-   `activity-log-resource` — the audit trail, with per-ability access.

## 🚀 Enabling it

1.  Uncomment `MoonOrbitServiceProvider::class` in `bootstrap/providers.php`.
2.  `php artisan migrate` — the tables live in this module and only load once the provider is active.
3.  `php artisan launch:permissions` — **with the module enabled.** It scans registered resources, so
    `ActivityLogResource.*` permissions don't exist otherwise and the menu item stays hidden for everyone.

Commenting the provider back out removes the menu, routes, commands and schedule entry, and leaves
the tables untouched.

## 🖥 Commands

```bash
php artisan media:sync                      # register disk files that have no Media row
php artisan media:sync --check              # list rows whose file is missing (exit 1)
php artisan orbit:activity:prune --days=90  # scheduled daily
```

## 📏 Limits

Uploads cap at 10 MB and only 13 extensions are accepted (`FileManagerPage::ALLOWED_EXTENSIONS`).
Soft delete keeps the file on disk; force delete removes it.

## 🖥 MediaField

Stores the **Media id**, not the URL:

```php
MediaField::make(__('File'), 'media_id')

Media::find($record->media_id)?->url()
```

## 🕓 Adding to the audit log

`ActivityObserver` is registered for `User`, `Role`, `Setting` and `Media`. To watch your own models,
register it from MoonOrbit — the dependency only points that way:

```php
// MoonOrbitServiceProvider::boot()
YourModel::observe(ActivityObserver::class);
```

Passwords, remember tokens and timestamps are never recorded.

## 📋 Requirements

Built against the same stack as the starter (PHP `^8.4.1`, Laravel `v13`, MoonShine `v4`), and
requires MoonLaunch.

## 🧪 Testing

```bash
vendor/bin/sail artisan test --testsuite=Modules
```

The suite is green whether the module is enabled or not: its tests remove themselves when the
provider is commented out.