---
paths:
  - 'modules/MoonLaunch/**'
  - 'modules/MoonLaunch/**/*.php'
---

# Moon Launch

## declare(strict_types=1) + handle(): int en MoonLaunch
Los ficheros de MoonLaunch (clases y traits) llevan `declare(strict_types=1);` obligatorio tras el `<?php`. Los comandos de consola de MoonLaunch usan `handle(): int` con `return self::SUCCESS;` final (un `handle(): int` sin return explícito lanza TypeError por el retorno implícito null).

## Los roles son Spatie/MoonShineRBAC: no existe columna role_id
Los usuarios NO tienen columna `role_id`: el rol viene de Spatie/MoonShineRBAC (pivot `model_has_roles`) y el Super Admin se identifica por `User::SUPER_ADMIN_ROLE_ID` (=1). Para preguntar si alguien tiene el rol usá `User::role(User::SUPER_ADMIN_ROLE_ID)->exists()` (el guard por defecto resuelve a 'moonshine' vía `guardName()`), nunca `where('role_id', ...)`: ese where viene copiado del `MakeUserCommand` viejo de MoonShine y revienta con "Unknown column 'role_id'".
