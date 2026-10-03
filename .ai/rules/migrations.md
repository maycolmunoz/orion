---
paths:
  - 'modules/**/database/migrations/**'
---

# Migrations

## Migraciones siempre dentro de su módulo
Toda migración nueva de un módulo vive en `modules/<Modulo>/database/migrations/` y se registra con `loadMigrationsFrom(__DIR__.'/../database/migrations')` en el provider (MoonOrbit ya lo hace; MoonLaunch hoy no tiene migraciones propias). No crear archivos en `database/migrations`: ahí quedan solo las tablas del skeleton (users, cache, jobs, permissions, notifications). Mover una migración ya aplicada es seguro si conserva el nombre de archivo: la tabla `migrations` la registra por nombre, no por ruta (así se movió `create_activity_logs_table` de MoonLaunch a MoonOrbit sin re-ejecutarse).
