---
paths:
  - 'modules/**/tests/**/*.php'
---

# Modules Tests

## Tests de módulo: declararlos en phpunit.xml y vincular TestCase en el archivo
phpunit.xml solo declara tests/Unit y tests/Feature; los tests de módulo viven en `modules/<Modulo>/tests/` y necesitan su propio testsuite (`<directory>modules/*/tests</directory>` con glob, para que los módulos futuros se autoconecten). Además `tests/Pest.php` usa `->in('Feature')`, ruta relativa a tests/, así que NO alcanza a los módulos: sin `uses(TestCase::class)` en el propio archivo, los tests corren contra PHPUnit\Framework\TestCase y fallan con "Target class [config] does not exist". Vincularlo en cada archivo de test del módulo: `uses(TestCase::class, LazilyRefreshDatabase::class);`.
