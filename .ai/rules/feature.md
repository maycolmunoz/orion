---
paths:
  - 'tests/Feature/**'
---

# Feature

## MoonShineAuth en tests: Auth::guard('moonshine')->setUser(), y MoonLaunch\Models\User::factory() no resuelve
Para probar páginas de MoonShine el guard hay que sembrarlo a mano: `Auth::guard('moonshine')->setUser($user)`. Los roles usan guard_name 'moonshine' (MoonshineRBACHasRoles::guardName() lee config('moonshine.auth.guard')). Ojo: `Modules\MoonLaunch\Models\User::factory()` falla con "Class Database\Factories\Modules\MoonLaunch\Models\UserFactory not found" — hereda `HasFactory` de App\Models\User pero no declara `newFactory()`, así que hay que crear el usuario con `User::create([...])`. Además `Tests\TestCase` fuerza ConfiguratorContract a singleton antes del boot (ver tests.md), así que los overrides de title/logo del provider y la autorización del vendor sí son observables en tests.
