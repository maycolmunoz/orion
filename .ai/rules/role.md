---
paths:
  - 'modules/MoonLaunch/MoonShine/Resources/Role/**'
---

# Role

## Proteger el rol Super Admin (id 1) contra borrado
El rol Super Admin (id = `User::SUPER_ADMIN_ROLE_ID` = 1) no se puede borrar: si se elimina, el RBAC deja de reconocer super admins (los detecta por id de rol, no por nombre). `RoleResource::delete()` y `massDelete()` lanzan `RuntimeException` con `ui.resource.super_admin_protected` para id 1 (el CrudController la convierte en toast de error). No eludir ese guard (no reintroducir borrado de id 1 vía Policies ni hooks de modelo): es la única barrera real.
