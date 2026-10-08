---
paths:
  - 'modules/**/MoonShine/**'
---

# MoonShine

Framework-level traps. Most of them fail silently (no exception, wrong output), so read this before touching a resource, page or field.

## Resource authorization is the `WithRolePermissions` marker, not Policies
Authorization comes from `sweet1s/moonshine-roles-permissions`: `AdminResource`, `RoleResource` and `ActivityLogResource` use the **marker** trait `WithRolePermissions` (empty in 4.0.0) and no `withPolicy()`, no custom Policies. `ModelResource::isCan()` applies the vendor's global rule: no marker means pass-through, marker means some role must satisfy `isHavePermission('Resource', 'ability')` (Super Admin = role id 1). The marker also (a) auto-hides the `MenuItem` without `viewAny` — never add a manual `canSee` — and (b) makes the role form list that resource's permissions. Trap: `isHavePermission()` **creates the missing permission in the DB** while checking, which is why `launch:permissions` must be run after adding a resource.

## Detail modal and delete actions are native in MoonShine 4.19
Modal detail is just `protected bool $detailInModal = true;` on the resource — the eye button fetches the DetailPage over AJAX (`fragment: 'crud-detail'`) into the `{uriKey}-detail-modal` container that `CrudPage::prepareRender` injects. Row delete and mass delete are built-in too: don't `except()` `Action::DELETE`/`Action::MASS_DELETE` in `activeActions()`. Don't hand-roll Modals or ActionButtons for any of this.

## A Page is not a ModelResource
`launch:permissions` creates no permission for a Page and `MenuRBAC::menu()` never filters it (it only checks `instanceof ModelResource`). Always register it with `$core->pages([...])` in the provider — otherwise `HasPageRequest::findPage()` cannot resolve the pageUri and returns 404. `MenuItem::canSee()` only hides the menu entry; it does not protect the URL.

## `MethodController` swallows every exception
It wraps the call in try/catch and turns any `Throwable` into HTTP 500, so `abort(403)` inside an `#[AsyncMethod]` arrives as a 500. Return `JsonResponse::make()->setStatusCode(403)->toast(...)` instead. Normal page render accepts `abort_unless(..., 403)` in `onLoad()`, but `onLoad()` does **not** run on the `method/{pageUri}` endpoint — guard those separately.

## Never persist the `hidden_<field>` helper
MoonShine renders new uploads as `<input type="file" name="logo">` and the already-stored path as `<input type="hidden" name="hidden_logo">`; clearing the file removes the whole div in JS, so `hidden_logo` simply does not arrive. Persist with `$request->hasFile('logo') ? store(...) : ($data['hidden_logo'] ?? null)`, keep the `image` rule conditional (`nullable`, since `logo` arrives as a string), and pass keys to `Setting::put()` **one by one** — spreading `$request->validate()` writes a garbage `hidden_logo` row on every save. Security variant: in `SettingsPage::saveSettings` read `$previousLogo = Setting::get('logo')` **before** `validate()` and constrain `hidden_logo` with `Rule::in(array_filter([$previousLogo]))`, or a super admin could inject an arbitrary path that the next `Storage::delete($previousLogo)` would delete.

## `Field::getData()` is a DataWrapper, not the model
In `changePreview(fn ($value, Field $field) => ...)` the second argument is not the model: `$field->getData()` returns a `ModelDataWrapper` (or a scalar on form pages). Use `$field->getData()?->getOriginal()` for the real model. The wrapper forwards `__get`, so `$field->getData()?->subject_type` works, but passing it to a typed parameter like `?ActivityLog` throws a TypeError.

## `changePreview` renders raw HTML
MoonShine's preview Blade uses `{!! !!}`, so anything from the DB that is not intentional HTML (e.g. a JSON diff) must be escaped with `e()`. For intentional markup return `Badge::make(...)->render()` as a string.

## `translatable()` prefixes the field name
`Field::translatable($ns)` resolves `__("$ns.{fieldName}")` through `WithLabel::getLabel()`, not `$ns` alone. A missing key renders the raw key (`moon-orbit::ui.activity_log.user_name`) without error, so create the key in **both** `en` and `es`.

## Nesting fields in `Box`/`Grid`/`Column`/`Tabs` is safe
`FormBuilder::prepareFields()` calls `Fields::fill()` → `onlyFields()` with `extractFields()`, which recurses through `HasFieldsContract` and `HasComponentsContract` and fills leaf fields in place. Value fill and attribute prep survive the nesting with no adjustments.

## Pagination and second-precision timestamps
The file manager paginates via `media()` → `LengthAwarePaginator` from `paginate(24)->withQueryString()`, rendered with `Paginator::make($media)` below the `CardsBuilder` (`grid($media->items())`, which accepts `iterable`). In tests, `latest()` ties on same-second timestamps — force distinct `created_at` when comparing pages.