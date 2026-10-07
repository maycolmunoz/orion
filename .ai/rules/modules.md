---
paths:
  - 'modules/**'
---

# Modules

## El código sin uso es punto de extensión, no código muerto
Orion es un STARTER KIT: el código sin consumidor es superficie de extensión, NO código muerto. MediaField, los setters sin llamar de WithProperties, los overrides no-op de AdminIndexPage/RoleIndexPage/AdminFormPage, los ejemplos comentados (LaunchPermissions:35-37, Image::make('avatar') en AdminFormPage) y las claves de lang sin uso (resource.system/role/avatar) existen a propósito para quien use el kit. No proponer borrarlos ni quitar hooks "para limpiar"; solo se eliminan si el usuario lo pide explícitamente. Ajustar/rellenar uno de esos huecos es trabajo válido.
