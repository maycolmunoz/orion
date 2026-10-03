<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\Models\User;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

function adminIndexUrl(): string
{
    return route('moonshine.resource.page', [
        'resourceUri' => 'admin-resource',
        'pageUri' => 'admin-index-page',
    ]);
}

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

    Auth::guard('moonshine')->setUser($user);

    $this->get(adminIndexUrl())->assertOk();

    $this->get(route('moonshine.resource.page', [
        'resourceUri' => 'role-resource',
        'pageUri' => 'role-index-page',
    ]))->assertOk();
});

it('denies users without roles', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);

    Auth::guard('moonshine')->setUser($user);

    $this->get(adminIndexUrl())->assertForbidden();
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

    Auth::guard('moonshine')->setUser($user);

    $this->get(adminIndexUrl())->assertOk();

    $this->get(route('moonshine.resource.page', [
        'resourceUri' => 'role-resource',
        'pageUri' => 'role-index-page',
    ]))->assertForbidden();
});
