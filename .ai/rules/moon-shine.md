---
paths:
  - 'modules/**/MoonShine/**'
---

# Moon Shine

## translatable() prefija el nombre del campo
`Field::translatable($ns)` resuelve `__("$ns.{fieldName}")` (WithLabel::getLabel), no `$ns` a secas: si falta la clave, el label se renderiza crudo (p. ej. `moon-orbit::ui.activity_log.user_name`) sin lanzar error. Al usar `translatable()`, crear la clave del nombre del campo en ambos idiomas (en/es).
