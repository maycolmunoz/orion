---
paths:
  - 'modules/**/tests/**/*.php'
---

# Modules Tests

## Tests de módulo: declararlos en phpunit.xml y vincular TestCase en el archivo
phpunit.xml solo declara tests/Unit y tests/Feature; los tests de módulo viven en `modules/<Modulo>/tests/` y necesitan su propio testsuite (`<directory>modules/*/tests</directory>` con glob, para que los módulos futuros se autoconecten). Además `tests/Pest.php` usa `->in('Feature')`, ruta relativa a tests/, así que NO alcanza a los módulos: sin `uses(TestCase::class)` en el propio archivo, los tests corren contra PHPUnit\Framework\TestCase y fallan con "Target class [config] does not exist". Vincularlo en cada archivo de test del módulo: `uses(TestCase::class, LazilyRefreshDatabase::class);`.

## Rutas y autorización de recursos MoonShine en tests
Usa los helpers de URL del resource, como en la doc de MoonShine: `app(XResource::class)->getIndexPageUrl()` y `->getDetailPageUrl($id)` (el resource se resuelve por contenedor; los métodos NO son estáticos). Para borrado: `->getRoute('crud.destroy', $id)` / `->getRoute('crud.massDelete')`. No hardcodear `route('moonshine.resource.page', ['resourceUri' => ..., 'pageUri' => ...])`. Autentica con `$this->be($user, 'moonshine')`. El endpoint moonshine.crud.index devuelve JSON y aborta 403 sin Accept: application/json. La autorización corre por el closure del vendor (ver moon-shine-resources.md) y el 403/200 se testea con HTTP real, pero el Super Admin se detecta por **id de rol** (`SUPER_ADMIN_ROLE_ID == $role->id`), no por nombre. MySQL no resetea AUTO_INCREMENT al hacer rollback en LazilyRefreshDatabase: sembrar el rol con `Role::forceCreate(['id' => User::SUPER_ADMIN_ROLE_ID, 'name' => 'Super Admin', 'guard_name' => 'moonshine'])` (Spatie guarda la PK, así que `create`/`firstOrCreate` ignoran `id`), o desde el segundo test que cree roles el id deriva y el super admin recibe 403 (el permiso ausente se auto-crea y devuelve false).
