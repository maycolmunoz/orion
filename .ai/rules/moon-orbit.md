---
paths:
  - 'modules/MoonOrbit/**'
---

# Moon Orbit

## Las migraciones de MoonOrbit viven en el módulo, no en database/migrations
Las migraciones de MoonOrbit van en `modules/MoonOrbit/database/migrations/` y se registran con `$this->loadMigrationsFrom()` en MoonOrbitServiceProvider::boot(). Así `migrate` a secas sigue corriendo y más adelante la condicionalidad cabe en un `if` antes de esa línea. `loadMigrationsFrom` usa `callAfterResolving('migrator')`, no requiere ser la primera statement. Regla general: toda migración nueva de un módulo vive dentro de su módulo; `database/migrations` queda solo para las tablas del skeleton (users, cache, jobs, permissions, notifications).

## El activity log completo vive en MoonOrbit (MoonLaunch no lo conoce)
`ActivityLog`, `ActivityObserver`, la migración `activity_logs` y el resource (con el marcador `WithRolePermissions`) viven en MoonOrbit. MoonLaunch (`User`, `Role`) NO importa nada de Orbit: los observers se registran con `User::observe()/Role::observe()/Setting::observe()` en MoonOrbitServiceProvider::boot() (dirección única Orbit → Launch, necesaria porque Orbit está previsto como condicional). No reintroducir un trait `LogsActivity` en MoonLaunch.

## Apariencia del panel: cablear por $config->set() con closures
title, logo, logo_small y palette se resuelven en MoonOrbitServiceProvider::boot() con closures; ConfiguratorContract::get() resuelve closures vía value(). AbstractLayout::colors() lee moonshineConfig()->getPalette() en cada render, así que un selector de paleta solo persiste el class-string (allowlist en SettingsPage::palettes()) y setea 'palette'. logo_small SIEMPRE debe setearse junto a logo, o en móvil/menú minimizado MoonShine muestra su propio logo. El footer de MoonShineLayout usa moonshineConfig()->getTitle() para reflejar el setting app_name.

## Layout global: setting layout + config layout_mode, sin clases nuevas
El selector persiste 'sidebar'/'topbar' en settings (allowlist SettingsPage::layouts()) y el provider setea 'layout_mode' (closure con fallback 'sidebar'). MoonShineLayout::build() voltea $topBar/$sidebar según moonshineConfig()->get('layout_mode'); la clase sigue final, no crear subclase. Login y ErrorPage tienen #[Layout] propio, no se ven afectados. El guardado devuelve JsonResponse::redirect() a la misma page para que paleta/layout apliquen y el preview se refresque (el toast apenas se ve).

## En tests, ConfiguratorContract singleton (ver tests.md)
MoonShineServiceProvider registra ConfiguratorContract como bind en tests, pero `Tests\TestCase` lo fuerza a singleton antes del boot; por eso el wiring por `$config->set()` y la autorización del vendor sí son observables en feature tests. Detalle y motivo en .ai/rules/tests.md.

## Sujeto del activity log: Modelo · Registro
La columna y el detalle del activity log muestran el sujeto como «Modelo · Registro» vía `ActivityLogResource::subjectPreview()` y `modelName()` (mapa traducido en `moon-orbit::ui.activity_log.models.{User,Role,Setting}`, fallback al `class_basename`). El filtro `subject_type` usa el mismo `modelName()`; no volver a mostrar `subject_label` solo, ni `class_basename` crudo.
