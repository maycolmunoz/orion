---
paths:
  - 'tests/**/*.php'
---

# Tests

## Usar LazilyRefreshDatabase: mockery no está instalado en require-dev
`RefreshDatabase` llama a `$this->artisan('migrate:fresh')`, que va por `PendingCommand::mockConsoleOutput()` y requiere `mockery/mockery` — no está en require-dev, así que cualquier feature test con BD muere con "Class Mockery not found". Usar `Illuminate\Foundation\Testing\LazilyRefreshDatabase`, que desactiva ese mock y además difiere el migrate:fresh hasta la primera query.
