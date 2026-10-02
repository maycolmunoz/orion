---
paths:
  - 'modules/MoonOrbit/**'
---

# Moon Orbit

## Las migraciones de MoonOrbit viven en el módulo, no en database/migrations
A diferencia de MoonLaunch (cuyas tablas están en database/migrations), las migraciones de MoonOrbit van en `modules/MoonOrbit/database/migrations/` y se registran con `$this->loadMigrationsFrom()` en MoonOrbitServiceProvider::boot(). Así `migrate` a secas sigue corriendo y más adelante la condicionalidad cabe en un `if` antes de esa línea. `loadMigrationsFrom` usa `callAfterResolving('migrator')`, no requiere ser la primera statement.

## Apariencia del panel: cablear por $config->set() con closures
title, logo, logo_small y palette se resuelven en MoonOrbitServiceProvider::boot() con closures; ConfiguratorContract::get() resuelve closures vía value(). AbstractLayout::colors() lee moonshineConfig()->getPalette() en cada render, así que un selector de paleta solo persiste el class-string (allowlist en SettingsPage::palettes()) y setea 'palette'. logo_small SIEMPRE debe setearse junto a logo, o en móvil/menú minimizado MoonShine muestra su propio logo. El footer de MoonShineLayout usa moonshineConfig()->getTitle() para reflejar el setting app_name.

## Layout global: setting layout + config layout_mode, sin clases nuevas
El selector persiste 'sidebar'/'topbar' en settings (allowlist SettingsPage::layouts()) y el provider setea 'layout_mode' (closure con fallback 'sidebar'). MoonShineLayout::build() voltea $topBar/$sidebar según moonshineConfig()->get('layout_mode'); la clase sigue final, no crear subclase. Login y ErrorPage tienen #[Layout] propio, no se ven afectados. El guardado devuelve JsonResponse::redirect() a la misma page para que paleta/layout apliquen y el preview se refresque (el toast apenas se ve).

## En tests, ConfiguratorContract usa bind: el wiring por config no es testeable
MoonShineServiceProvider registra ConfiguratorContract como singleton en prod pero bind cuando runningUnitTests(). Por eso los $config->set() del provider (title, logo, palette, layout_mode) no sobreviven a una segunda resolución en feature tests: un test que haga moonshineConfig()->get('layout_mode') ve el default. El wiring se verifica en tinker (dev usa singleton); en tests solo se cubre persistencia/validación/HTTP.
