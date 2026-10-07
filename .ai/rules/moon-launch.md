---
paths:
  - 'modules/MoonLaunch/**'
---

# Moon Launch

## declare(strict_types=1) + handle(): int en MoonLaunch
Los ficheros de MoonLaunch (clases y traits) llevan `declare(strict_types=1);` obligatorio tras el `<?php`. Los comandos de consola de MoonLaunch usan `handle(): int` con `return self::SUCCESS;` final (un `handle(): int` sin return explícito lanza TypeError por el retorno implícito null).
