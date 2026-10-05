<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\Models\User;
use Modules\MoonLaunch\MoonShine\Resources\Admin\AdminResource;
use Modules\MoonLaunch\MoonShine\Resources\Role\RoleResource;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('allows super admins to manage users and roles', function () {
    $role = Role::query()
        ->where('name', 'Super Admin')
        ->where('guard_name', 'moonshine')
        ->first()
        ?? Role::forceCreate([
            'id' => User::SUPER_ADMIN_ROLE_ID,
            'name' => 'Super Admin',
            'guard_name' => 'moonshine',
        ]);

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
