---
paths:
  - 'modules/**'
---

# Modules

Orion is a STARTER KIT: the code lives in modules, and any consumer of it may download a module folder and drop it into their own project.

## Unused code is extension surface, not dead code
`MediaField`, the uncalled `WithProperties` setters, the no-op overrides in `AdminIndexPage`/`RoleIndexPage`/`AdminFormPage`, the commented examples (`LaunchPermissions`, `Image::make('avatar')`) and unused lang keys (`resource.system`, `role`, `avatar`) exist on purpose for whoever uses the kit. Never propose deleting them or removing hooks to "clean up" — only if the user asks explicitly. Filling one of those gaps is valid work.

## Every module migration lives inside the module
`modules/<Module>/database/migrations/`, registered with `loadMigrationsFrom(__DIR__.'/../database/migrations')` in that module's provider. `database/migrations/` holds only skeleton tables (users, cache, jobs, permissions, notifications). Moving an already-applied migration is safe if the filename is kept — the `migrations` table records by name, not by path (that's how `create_activity_logs_table` moved from MoonLaunch to MoonOrbit without re-running).

## Dependencies point one way: optional module → core module
MoonOrbit observes `User`/`Role` from MoonLaunch via `User::observe()`/`Role::observe()` in `MoonOrbitServiceProvider::boot()`. MoonLaunch must never import anything from MoonOrbit, so MoonOrbit can stay conditional. The full activity log (`ActivityLog`, `ActivityObserver`, its migration, its resource) belongs to MoonOrbit; do not reintroduce a `LogsActivity` trait in MoonLaunch.