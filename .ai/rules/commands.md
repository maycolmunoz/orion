---
paths:
  - modules/MoonLaunch/Console/Commands/LaunchInstall.php
---

# Commands

## Idempotencia launch:install: key + rbac user guard
Bugs corregidos en launch:install (idempotencia):
1. key:generate — ahora solo se ejecuta si config('app.key') no tiene un valor base64 válido (comprobado con Str::is('base64:*', config('app.key'))). Evita invalidar sesiones/cookies/ datos cifrados en apps ya instaladas.
2. moonshine-rbac:user — ahora solo se ejecuta si no existe un usuario con role_id=1 (super admin). Evita crear admin duplicados en ejecuciones múltiples.
Ambas guardas están en handle(): int, devuelven SUCCESS y info al usuario cuando se omite la operación.
