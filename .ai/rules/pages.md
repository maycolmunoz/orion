---
paths:
  - 'modules/**/MoonShine/Pages/*.php'
---

# Pages

## Custom Page: registrar con $core->pages() y usar closures para leer BD en render
Una Page de MoonShine NO es un ModelResource: `launch:permissions` no le crea permiso y `MenuRBAC::menu()` no la filtra (solo mira `instanceof ModelResource`). Registrar siempre con `$core->pages([...])` en el provider, si no `HasPageRequest::findPage()` no resuelve el pageUri y da 404. Para leer BD en branding usar closures perezosas: `$config->set('title', fn () => ...)` — `MoonShineConfigurator::get()` hace `value()` sobre el item, así la BD se toca en render y no en boot().

## MethodController traga toda excepción: 403 en async method debe ser JsonResponse
`MethodController::__invoke` envuelve la llamada en try/catch y convierte cualquier Throwable en HTTP 500, así que `abort(403)` DENTRO de un `#[AsyncMethod]` llega como 500. Devolver `JsonResponse::make()->setStatusCode(403)->toast(...)`. El render normal de la Page sí acepta `abort_unless(..., 403)` en `onLoad()` (lo invoca `prepareBeforeRender()`), pero `onLoad()` NO corre en el endpoint `method/{pageUri}`: hay que guardarlos por separado. `MenuItem::canSee()` solo oculta el ítem del menú, no protege la URL.

## En Forms de Page: MoonShine manda el archivo existente en hidden_<column>, no en <column>
El campo `File`/`Image` renderiza `<input type="file" name="logo">` para uploads nuevos y un `<input type="hidden" name="hidden_logo">` con la ruta ya guardada (FileTrait::getHiddenRemainingValuesName()). Al quitar el archivo el JS borra el div completo, así que `hidden_logo` simplemente no llega. Por eso: `logo` viene en `$request->file('logo')` y el valor a persistir es `$request->hasFile('logo') ? store(...) : ($data['hidden_logo'] ?? null)`. La regla `image` NO puede ir sin condición: si `logo` llega como string falla, por eso se valida con `nullable`.

## Nunca persistir el helper hidden_<campo> de los File fields
Hacer `Setting::put([...$data, ...])` sobre el resultado de `$request->validate()` persiste también `hidden_logo`, dejando una fila basura por cada guardado (y desactualizada, porque el hidden aún apunta al archivo anterior). Pasar siempre las claves una por una: `hidden_logo` se lee como origen del valor pero nunca se escribe. Cubierto por el test "never persists the hidden_logo form helper as a setting".

## Anidar campos en Box/Grid/Column/Tabs es seguro: el fill es recursivo
FormBuilder::prepareFields() usa Fields::fill() → onlyFields() con extractFields(), que baja recursivamente por HasFieldsContract y HasComponentsContract y llena los campos hoja EN SITIO. Por eso envolver campos en Box/Grid/Column/Flex/Tabs mantiene el fill de valores (y prepareAttributes) sin ajustes. Verificado: test 'lets a super admin open the settings page' siembra app_name y asserta value="Orion" dentro de Box→Grid→Column.
