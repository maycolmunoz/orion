---
paths:
  - 'modules/**/Providers/*.php'
---

# Providers

## Module provider boot() is a coordinator of register* helpers
Both module providers keep boot() to translations/migrations plus one line per concern: registerObservers(), registerCommands(), registerPages($core), registerResources($core), registerMenu($menu), registerConfig($config). Name each helper after the registry it writes to, never a vague umbrella: `registerComponents()` covering pages+resources in one module and resources only in another was the trap. MoonLaunch has no registerPages() because it registers no pages. Translators stay inline.
