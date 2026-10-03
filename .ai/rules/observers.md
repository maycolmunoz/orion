---
paths:
  - 'modules/MoonOrbit/Observers/**'
---

# Observers

## ActivityObserver: eventos y forceDelete sin duplicar
El audit log usa `ActivityObserver` (MoonOrbit) registrado por `User::observe()/Role::observe()/Setting::observe()` en MoonOrbitServiceProvider; MoonLaunch no importa nada de Orbit. En SoftDeletes, `forceDelete()` dispara `deleted` (con `isForceDeleting()` true) y luego `forceDeleted`: el observer ignora `deleted` si está force-deleting y loguea `forceDeleted` aparte. `restored`/`forceDeleted` simplemente no disparan en modelos sin SoftDeletes; no hace falta `registerModelEvent` (eso era del trait eliminado). El label del sujeto es `name ?? getKey()` (Setting cae a su key).
