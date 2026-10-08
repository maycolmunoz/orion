# Changelog

## [1.1.1] - 2026-10-08

### Security

-   `league/commonmark` 2.10.0 → 2.10.3 — quadratic-time DoS in the GFM table extension (high), DisallowedRawHtml bypass (medium)
-   `shell-quote` 1.9.0 → 1.12.0 — command injection via `quote()` (critical)
-   `source-map-js` 1.2.1 → 1.2.2 — event-loop DoS through indexed source-map offsets (high)

## [1.1.0] - 2026-10-08

MoonOrbit, an optional module shipped disabled by default. Requires PHP 8.4.1+.

### Added

-   MoonOrbit: optional module with its own provider, migrations and translations
-   Settings page for branding and layout options
-   Activity log for MoonLaunch models
-   Media library backed by the database, with trash
-   `media:sync` — recursive sync, `--check` to list rows whose file is missing
-   `orbit:activity:prune --days=90`
-   Upload limits: 10 MB, 13 extensions
-   Testsuite `Modules` in `phpunit.xml`; module tests skip when the provider is disabled
-   CI job running the test suite on MySQL 8.0

### Fixed

-   Super admin detected through the Spatie role instead of a nonexistent `role_id` column

### Changed

-   Root `README.md` is now only an entry point; each module ships a self-contained README
-   `.ai/rules` consolidated from 19 files to 7, in English

## [1.0.0] - 2026-09-29

First release. Requires PHP 8.4.1+.

-   Laravel 13, MoonShine 4.19.4, Tailwind CSS 4
-   Roles and permissions
-   `php artisan launch:install` — installs everything
-   `php artisan launch:permissions` — regenerates permissions
-   Preconfigured panel: layout, menu, dashboard, login
-   `AdminResource`, `RoleResource`, `PermissionResource`
-   Dashboard with admins, roles, permissions and stack versions
-   Traits `WithProperties`, `WithSoftDeletes`, `WithTrashedQuery`
-   English and Spanish
