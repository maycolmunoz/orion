---
paths:
  - 'modules/MoonOrbit/MoonShine/Pages/**'
---

# Moon Shine Pages

## File Manager: disco public/ dir files, sin tabla y ruta saneada
FileManagerPage (MoonOrbit, Super Admin only como SettingsPage) trabaja directo con Storage::disk('public') en 'files/', plano y sin modelo ni migración: la lista sale de $disk->files('files') y las subidas de un File field multiple (se guardan en uploadFiles con ->store). No añadir tabla de metadatos salvo necesidad real. deleteFile recibe 'path' del cliente, así que isManagedPath() exige no vacío, sin '..', prefijo 'files/' y exists antes de borrar (evita path traversal); los métodos async devuelven JsonResponse 403/422 con toast, nunca abort().
