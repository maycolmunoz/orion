<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\Models\User;
use Modules\MoonLaunch\MoonShine\Resources\Admin\AdminResource;
use Modules\MoonLaunch\MoonShine\Resources\Admin\Pages\AdminIndexPage;
use Modules\MoonLaunch\MoonShine\Resources\Role\RoleResource;
use MoonShine\Contracts\Core\DependencyInjection\RouterContract;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

function superAdminRole(): Role
{
    return Role::query()
        ->where('name', 'Super Admin')
        ->where('guard_name', 'moonshine')
        ->first()
        ?? Role::forceCreate([
            'id' => User::SUPER_ADMIN_ROLE_ID,
            'name' => 'Super Admin',
            'guard_name' => 'moonshine',
        ]);
}

function asyncMethodUrl(string $method, int $item): string
{
    return app(RouterContract::class)->getEndpoints()->method(
        method: $method,
        params: ['resourceItem' => $item],
        page: app(AdminIndexPage::class),
        resource: app(AdminResource::class),
    );
}

/**
 * The trash list is a lazy component, so the buttons only render on the component request.
 * Locale is forced because ChangeLocale runs during the request, not before it.
 */
function trashUrl(): string
{
    app()->setLocale(moonshineConfig()->getLocale());

    $resource = app(AdminResource::class);
    $page = $resource->getIndexPage();

    return route('moonshine.component', [
        '_component_name' => $page->getListComponentName(),
        'pageUri' => $page->getUriKey(),
        'resourceUri' => $resource->getUriKey(),
        'query-tag' => collect($resource->getQueryTags())->first()->getUri(),
    ]);
}

it('allows super admins to manage users and roles', function () {
    $role = superAdminRole();

    $user = User::create([
        'name' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);

    $user->assignRole($role);

    $this->be($user, 'moonshine');

    $this->get(app(AdminResource::class)->getIndexPageUrl())->assertOk();
    $this->get(app(RoleResource::class)->getIndexPageUrl())->assertOk();
});

it('denies users without roles', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);

    $this->be($user, 'moonshine');

    $this->get(app(AdminResource::class)->getIndexPageUrl())->assertForbidden();
});

it('grants access to roles with the matching permission', function () {
    $permission = Permission::findOrCreate('AdminResource.viewAny', 'moonshine');

    $role = Role::findOrCreate('Editor', 'moonshine');
    $role->givePermissionTo($permission);

    $user = User::create([
        'name' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);

    $user->assignRole($role);

    $this->be($user, 'moonshine');

    $this->get(app(AdminResource::class)->getIndexPageUrl())->assertOk();
    $this->get(app(RoleResource::class)->getIndexPageUrl())->assertForbidden();
});

it('forbids async restore and force delete without the permissions', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);

    $this->be($user, 'moonshine');

    $trashed = User::create([
        'name' => 'Trashed',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);
    $trashed->delete();

    $this->post(asyncMethodUrl('restore', $trashed->id))->assertForbidden();
    $this->post(asyncMethodUrl('forceDelete', $trashed->id))->assertForbidden();

    expect(User::withTrashed()->find($trashed->id)?->trashed())->toBeTrue();
});

it('lets a super admin restore and force delete trashed users', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);
    $user->assignRole(superAdminRole());

    $this->be($user, 'moonshine');

    $restored = User::create([
        'name' => 'Restored',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);
    $restored->delete();

    $purged = User::create([
        'name' => 'Purged',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);
    $purged->delete();

    $this->post(asyncMethodUrl('restore', $restored->id))->assertOk();

    expect(User::find($restored->id)?->trashed())->toBeFalse();

    $this->post(asyncMethodUrl('forceDelete', $purged->id))->assertOk();

    $this->assertDatabaseMissing('users', ['id' => $purged->id]);
});

it('shows the trash actions to a super admin', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);
    $user->assignRole(superAdminRole());

    $this->be($user, 'moonshine');

    User::create([
        'name' => 'Trashed',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ])->delete();

    $this->get(trashUrl())
        ->assertOk()
        ->assertSee(__('moon-launch::ui.soft_deletes.force_delete'));
});

it('hides the trash actions without the permissions', function () {
    $permission = Permission::findOrCreate('AdminResource.viewAny', 'moonshine');

    $role = Role::findOrCreate('Editor', 'moonshine');
    $role->givePermissionTo($permission);

    $user = User::create([
        'name' => 'Editor',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);
    $user->assignRole($role);

    $this->be($user, 'moonshine');

    User::create([
        'name' => 'Trashed',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ])->delete();

    $this->get(trashUrl())
        ->assertOk()
        ->assertDontSee(__('moon-launch::ui.soft_deletes.force_delete'));
});
