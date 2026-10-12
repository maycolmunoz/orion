<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\Models\User;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

function userWithRole(Role $role): User
{
    $user = User::create([
        'name' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);

    $user->assignRole($role);

    return $user;
}

it('grants super admin to the role id even when it is renamed', function () {
    $role = Role::forceCreate([
        'id' => User::SUPER_ADMIN_ROLE_ID,
        'name' => 'Root',
        'guard_name' => 'moonshine',
    ]);

    expect(userWithRole($role)->isSuperAdmin())->toBeTrue();
});

it('denies super admin to a lookalike role with a different id', function () {
    $role = Role::forceCreate([
        'id' => User::SUPER_ADMIN_ROLE_ID + 1,
        'name' => 'Super Admin',
        'guard_name' => 'moonshine',
    ]);

    expect(userWithRole($role)->isSuperAdmin())->toBeFalse();
});

it('denies super admin to a user without roles', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);

    expect($user->isSuperAdmin())->toBeFalse();
});
