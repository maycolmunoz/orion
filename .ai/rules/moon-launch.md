---
paths:
  - 'modules/MoonLaunch/**'
---

# MoonLaunch

The core module. Always enabled — it is the app's backbone, not an option.

## Strict types and command return types are mandatory
Every class and trait carries `declare(strict_types=1);` right after `<?php`. Console commands use `handle(): int` and must end with `return self::SUCCESS;` — an explicit-less `handle(): int` throws a TypeError on the implicit null return.

## Roles are Spatie; there is no `role_id` column
Users have NO `role_id`: the role lives in Spatie's `model_has_roles` pivot, and the Super Admin is identified by role id (`User::SUPER_ADMIN_ROLE_ID === 1`), not by name. Ask `User::role(User::SUPER_ADMIN_ROLE_ID)->exists()` (the default guard resolves to `moonshine` via `guardName()`), never `where('role_id', ...)` — that where is copied from MoonShine's old `MakeUserCommand` and dies with `Unknown column 'role_id'`.

## Super Admin role (id 1) can never be deleted
`RoleResource::delete()` and `massDelete()` throw `RuntimeException` with `ui.resource.super_admin_protected` for id 1 (the CrudController turns it into an error toast). Deleting it would leave RBAC with no recognized super admins. Don't route around that guard — no Policies, no model hooks; it is the only real barrier.

## `launch:install` must stay idempotent
Both guards live in `handle(): int`, return `SUCCESS`, and `info()` the user when they skip work:
1. `key:generate` runs only when `config('app.key')` is not valid, checked with `Str::is('base64:*', config('app.key'))` — regenerating invalidates sessions, cookies and encrypted data in an installed app.
2. `moonshine-rbac:user` runs only when no super admin exists.