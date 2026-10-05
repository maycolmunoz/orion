---
paths:
  - 'tests/Feature/**'
---

# Feature

## MoonShineAuth en tests: usar `$this->be($user, 'moonshine')`, y MoonLaunch\Models\User::factory() no resuelve
Para probar páginas de MoonShine autentica con `$this->be($user, 'moonshine')` (es lo que recomienda la doc de MoonShine; equivale a `Auth::guard('moonshine')->setUser()` pero además fija el guard por defecto). Los roles usan guard_name 'moonshine' (MoonshineRBACHasRoles::guardName() lee config('moonshine.auth.guard')). Ojo: `Modules\MoonLaunch\Models\User::factory()` falla con "Class Database\Factories\Modules\MoonLaunch\Models\UserFactory not found" — hereda `HasFactory` de App\Models\User pero no declara `newFactory()`, así que hay que crear el usuario con `User::create([...])`. Los helpers `loginAsUser(TestCase $test)/loginAsSuperAdmin(TestCase $test)` (MoonOrbit) reciben el `$test` para llamar `$test->be()`. Además `Tests\TestCase` fuerza ConfiguratorContract a singleton antes del boot (ver tests.md), así que los overrides de title/logo del provider y la autorización del vendor sí son observables en tests.
