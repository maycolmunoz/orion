---
paths:
  - 'modules/MoonOrbit/MoonShine/Resources/**'
---

# Resources

## changePreview renderiza HTML crudo: escapar valores
El blade de preview de MoonShine usa {!! !!} (sin escapar). Todo dato que venga de la BD y no sea HTML intencional (p. ej. el JSON de changes) debe escaparse con e() dentro de changePreview; para badges/HTML intencional devolver Badge::make(...)->render() como (string).
