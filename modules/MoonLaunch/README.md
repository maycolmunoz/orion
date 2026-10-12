# MoonLaunch

Core of the starter kit: admin users, roles, permissions, the dashboard, and the commands that
install it. **Not optional** — it is the app's backbone. MoonOrbit adds optional extras on top.

## 📦 What you get

-   `Dashboard` — admin/role/permission counts, 5 latest admins, stack versions
-   `AdminResource` — user CRUD with soft deletes
-   `RoleResource` — role CRUD with the per-role permission form
-   `PermissionResource` — from `moonshine-roles-permissions`
-   `WithProperties`, `WithSoftDeletes`, `WithTrashedQuery` — traits for your own resources

## 🚀 Installation

```bash
php artisan launch:install
```

Generates the app key if missing, migrates, runs `launch:permissions`, then creates the Super Admin.
Re-runnable. `moonshine-rbac:user` is interactive, so the last step only waits for input when no
super admin exists yet.

```bash
php artisan launch:permissions
```

Regenerates permissions for every registered resource, then recreates `Super Admin` with all of them
(see [`LaunchPermissions`](Console/Commands/LaunchPermissions.php)). Re-run it after adding a resource —
and note it only sees resources whose module provider is active.

## 🧩 Traits

| Trait              | Goes on            | What it adds                                                                                       |
| ------------------ | ------------------ | -------------------------------------------------------------------------------------------------- |
| `WithProperties`   | Resource           | Fluent `protected` setters from the resource constructor: `->title()`, `->column()`, `->with()`, `->redirectAfterSave()`, `->itemsPerPage()`, … |
| `WithTrashedQuery` | Resource           | `withTrashed()`, so the resource resolves soft-deleted items                                        |
| `WithSoftDeletes`  | **Index page**     | Trash UI: a `Deleted` tag, plus restore / force-delete actions gated by ability                     |

`WithSoftDeletes` is not a resource trait — put it on the index page or nothing happens. Working
examples: `AdminResource` + `AdminIndexPage`, `RoleResource`, `ActivityLogResource`.

## 🔐 Super admin

```php
use Modules\MoonLaunch\Models\User;

// Does any super admin exist? (used by launch:install)
User::role(User::SUPER_ADMIN_ROLE_ID)->exists();   // User::SUPER_ADMIN_ROLE_ID === 1

// Is THIS user a super admin? (used by the pages and the menu)
$user->isSuperAdmin();
```

> There is **no `role_id` column** on `users`. Roles live in the Spatie pivot tables, so
> `where('role_id', 1)` returns zero rows instead of erroring.

`isSuperAdmin()` checks the role **id**, never its name. The name is editable — only deleting the
role is blocked — so `hasRole('Super Admin')` breaks the moment the role is renamed, and any other
role carrying that name inherits the access.

Permission names are `<Resource>.<ability>`. `RoleResource` refuses to delete or mass-delete the
Super Admin role.

Built against the same stack as the starter (PHP `^8.4.1`, Laravel `v13`, MoonShine `v4`).

## 🧪 Testing

```bash
vendor/bin/sail artisan test --testsuite=Modules
```

Mockery is **not** installed, so `$this->artisan()` is unavailable — assert commands with
`Artisan::call()` and `Artisan::output()`.