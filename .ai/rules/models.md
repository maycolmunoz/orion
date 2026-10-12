---
paths:
  - 'modules/**/Models/User.php'
---

# Models

## Ask for Super Admin with User::isSuperAdmin(), never hasRole('Super Admin')
Ask `MoonShineAuth::getGuard()->user()?->isSuperAdmin() === true`; it checks `hasRole(User::SUPER_ADMIN_ROLE_ID)` by primary key. Never `hasRole('Super Admin')` — the role NAME is editable (RoleResource only blocks deleting id 1), so a rename silently locks the real super admin out of the settings page and file manager, and a second role named 'Super Admin' grants full file access. Spatie matches by id for free: `hasRole(int)` hits the `is_int` branch in HasRoles.
