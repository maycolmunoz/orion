---
paths:
  - 'modules/**/MoonShine/Resources/**'
---

# Moon Shine Resources

## Autorización de resources: trait marcador WithRolePermissions (vía paquete)
La autorización va por la vía de sweet1s/moonshine-roles-permissions 4.0.0: AdminResource, RoleResource y ActivityLogResource usan el trait **marcador** `WithRolePermissions` (vacío en 4.0.0) y NO usan `->withPolicy()` ni Policies propias. `ModelResource::isCan()` evalúa la regla global del vendor: sin marcador deja pasar; con marcador exige que algún rol del usuario supere `isHavePermission('Resource', 'ability')` (Super Admin = rol con id 1). El mismo marcador (a) auto-oculta el MenuItem sin `viewAny` — no añadir `canSee` manual, y (b) hace que el formulario de Rol (`WithPermissionsFormComponent`) liste los permisos del resource; sin él no aparecen. Trampa: `isHavePermission()` crea en BD el permiso `Resource.ability` que falte durante la comprobación (`launch:permissions` los pre-genera; para tests ver modules-tests.md). No reintroducir Policies ni `withPolicy()`.

## Detalle en modal y borrado nativos (detailInModal / activeActions)
En MoonShine 4.19 el detalle en modal es nativo: `protected bool $detailInModal = true;` en el resource. El botón del ojo (DetailButton) carga por AJAX la DetailPage (`fragment: 'crud-detail'`) dentro del contenedor `{uriKey}-detail-modal` que inyecta CrudPage::prepareRender; no crear Modal ni ActionButton a mano. Borrado por fila y masivo también son built-in: basta no excluir `Action::DELETE`/`Action::MASS_DELETE` en `activeActions()->except(...)`; los botones traen confirmación modal y respetan la policy (`delete`/`massDelete`).
