---
paths:
  - 'tests/**/*.php'
---

# Tests

## Usar LazilyRefreshDatabase: mockery no está instalado en require-dev
`RefreshDatabase` llama a `$this->artisan('migrate:fresh')`, que va por `PendingCommand::mockConsoleOutput()` y requiere `mockery/mockery` — no está en require-dev, así que cualquier feature test con BD muere con "Class Mockery not found". Usar `Illuminate\Foundation\Testing\LazilyRefreshDatabase`, que desactiva ese mock y además difiere el migrate:fresh hasta la primera query.

## TestCase fuerza ConfiguratorContract singleton antes del boot
MoonShineServiceProvider registra `ConfiguratorContract` con `bind` en `runningUnitTests()`, así que la regla de autorización del vendor y los `$config->set()` (title, logo, palette, layout_mode) registrados en el boot se perdían al re-resolverlo en cada request. `Tests\TestCase::createApplication()` lo rebindea a `singleton` en `$app->booting()` (tras todos los `register()`, antes de los `boot()`), igual que producción; sin eso los tests HTTP de 403/200 de MoonShine pasan todo. No volver a `bind` ni mover este override a un `register()` posterior (quedaría pisado).

## Mockery no está instalado: usar Artisan::call, no $this->artisan()->assert*
Mockery no está en composer.json ni en vendor, y el `PendingCommand` de Laravel lo necesita: `$this->artisan('x')->assertSuccessful()` / `expectsOutput*` muere con `Class "Mockery" not found`. Para probar comandos usá el facade: `expect(Artisan::call('cmd'))->toBe(0)->and(Artisan::output())->toContain('...')`. No agregar mockery como dependencia sin aprobación.
