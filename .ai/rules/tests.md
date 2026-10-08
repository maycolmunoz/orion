---
paths:
  - 'tests/**'
  - 'modules/**/tests/**/*.php'
---

# Tests

Mockery is **not** installed, and `LazilyRefreshDatabase` is mandatory. Both are load-bearing.

## Use `LazilyRefreshDatabase`, and assert commands with `Artisan::call`
`RefreshDatabase` runs `$this->artisan('migrate:fresh')`, which goes through `PendingCommand::mockConsoleOutput()` and needs `mockery/mockery` — absent from `require-dev`, so any DB feature test dies with `Class Mockery not found`. `LazilyRefreshDatabase` skips that mock and defers `migrate:fresh` to the first query. For commands, `$this->artisan('x')->assertSuccessful()` and `->expectsOutput*()` also need Mockery: use the facade — `expect(Artisan::call('cmd'))->toBe(0)->and(Artisan::output())->toContain('...')`. Never add Mockery as a dependency without approval.

## `TestCase` forces `ConfiguratorContract` to a singleton before boot
`MoonShineServiceProvider` binds `ConfiguratorContract` inside `runningUnitTests()`, so re-resolving it per request threw away the vendor's authorization rule and every `$config->set()` registered in `boot()` (title, logo, palette, layout_mode) — the 403/200 HTTP tests all passed. `Tests\TestCase::createApplication()` rebinds it as `singleton` in `$app->booting()`, i.e. after all `register()` and before any `boot()`, exactly like production. Don't switch back to `bind` and don't move this override into a later `register()`, or it gets overwritten.

## Module test files need their own wiring
`phpunit.xml` declares only `tests/Unit` and `tests/Feature`; module tests live in `modules/<Module>/tests/` and need their own testsuite with a glob (`<directory>modules/*/tests</directory>`) so future modules self-register. `tests/Pest.php` uses `->in('Feature')`, a path relative to `tests/`, so it does not reach the modules: without `uses(TestCase::class, LazilyRefreshDatabase::class)` **in the module's own file**, its tests run against `PHPUnit\Framework\TestCase` and fail with `Target class [config] does not exist`.

## Conditional module tests unregister the whole file
Each module test file opens with `require_once __DIR__.'/helpers.php';` then `if (! moonOrbitIsActive()) { return; }`. `moonOrbitIsActive()` (in `modules/MoonOrbit/tests/Feature/helpers.php`) reads `bootstrap/providers.php` and is false when `MoonOrbitServiceProvider` is not registered. The early return means the file defines no `it()`, so those tests disappear from the run. The guard must be at file level because Pest loads test files *before* creating the app — `app()` does not exist yet, which is also why the helper reads the providers file instead of calling `app()->providerIsLoaded()`. Do not use `markTestSkipped` from a `beforeEach` in `tests/Pest.php` with `->in('../modules/...')`: Pest 4 reports WARN and still lists the tests.

## Authenticate with `$this->be($user, 'moonshine')`, and build users with `create()`
`$this->be($user, 'moonshine')` is what MoonShine's docs recommend; it also sets the default guard, which the role lookups need (`guard_name` is `moonshine`, resolved by `MoonshineRBACHasRoles::guardName()`). Only **one** `$this->be()` per test — switching user mid-test yields a 500 or redirect. `Modules\MoonLaunch\Models\User::factory()` fails with `Class Database\Factories\...\UserFactory not found` because it inherits `HasFactory` from `App\Models\User` without declaring `newFactory()`, so create users with `User::create([...])`. MoonOrbit's `loginAsUser(TestCase $test)` / `loginAsSuperAdmin(TestCase $test)` helpers take the test so they can call `$test->be()`.

## Get resource URLs from the resource, never hardcoded
Use `app(XResource::class)->getIndexPageUrl()` and `->getDetailPageUrl($id)`; the resource resolves through the container and these methods are **not** static. Deletes go through `->getRoute('crud.destroy', $id)` / `->getRoute('crud.massDelete')`. Never hardcode `route('moonshine.resource.page', ['resourceUri' => ..., 'pageUri' => ...])`. Authorization runs in the vendor's closure, so assert 403/200 over real HTTP. `moonshine.crud.index` returns JSON and aborts 403 without `Accept: application/json`.

## Seed the Super Admin with a forced id
MySQL does not reset `AUTO_INCREMENT` on rollback, so after the first test that creates roles the id drifts and the super admin gets a 403 (the missing permission self-creates and evaluates false). Seed explicitly: `Role::forceCreate(['id' => User::SUPER_ADMIN_ROLE_ID, 'name' => 'Super Admin', 'guard_name' => 'moonshine'])` — Spatie stores the primary key, so plain `create`/`firstOrCreate` ignore `id`. Super Admin is always detected by role **id**, never by name.

## Async methods, lazy lists and locale
1. Async method URL: `app(RouterContract::class)->getEndpoints()->method($method, params: ['resourceItem' => $id], page: app(XPage::class), resource: app(XResource::class))` — page and resource are **objects**, not strings.
2. Lists with `isLazy = true` only render rows/actions inside `route('moonshine.component', [...])`; `getIndexPageUrl()` returns the shell. Pass `_component_name => $resource->getIndexPage()->getListComponentName()` and derive `pageUri`/`resourceUri` from the resource.
3. `ChangeLocale` runs *inside* the request: for URLs with `query-tag` or translated text, call `app()->setLocale(moonshineConfig()->getLocale())` **before** generating them, or the slug does not match and the tag never activates.