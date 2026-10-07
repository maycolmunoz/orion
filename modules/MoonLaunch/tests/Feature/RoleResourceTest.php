<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\Models\User;
use Modules\MoonLaunch\MoonShine\Resources\Role\RoleResource;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

function roleResource(): RoleResource
{
    return app(RoleResource::class);
}

function roleAdmin(): User
{
    $user = User::create([
        'name' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);

    $user->assignRole(
        Role::forceCreate([
            'id' => User::SUPER_ADMIN_ROLE_ID,
            'name' => 'Super Admin',
            'guard_name' => 'moonshine',
        ])
    );

    return $user;
}

it('prevents deleting the Super Admin role', function () {
    $this->be(roleAdmin(), 'moonshine');

    $this->deleteJson(roleResource()->getRoute('crud.destroy', User::SUPER_ADMIN_ROLE_ID))
        ->assertStatus(500)
        ->assertJson(['message' => __('moon-launch::ui.resource.super_admin_protected')]);

    $this->assertDatabaseHas('roles', ['id' => User::SUPER_ADMIN_ROLE_ID]);
});

it('lets roles other than the Super Admin be deleted', function () {
    $this->be(roleAdmin(), 'moonshine');

    $role = Role::create(['name' => 'Lector', 'guard_name' => 'moonshine']);

    $this->deleteJson(roleResource()->getRoute('crud.destroy', $role->id))->assertOk();

    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

it('prevents mass deleting a selection that includes the Super Admin role', function () {
    $this->be(roleAdmin(), 'moonshine');

    $other = Role::create(['name' => 'Lector', 'guard_name' => 'moonshine']);

    $this->deleteJson(roleResource()->getRoute('crud.massDelete'), [
        'ids' => [(string) User::SUPER_ADMIN_ROLE_ID, (string) $other->id],
    ])
        ->assertStatus(500)
        ->assertJson(['message' => __('moon-launch::ui.resource.super_admin_protected')]);

    $this->assertDatabaseHas('roles', ['id' => User::SUPER_ADMIN_ROLE_ID]);
    $this->assertDatabaseHas('roles', ['id' => $other->id]);
});

it('mass deletes roles when the Super Admin role is not selected', function () {
    $this->be(roleAdmin(), 'moonshine');

    $one = Role::create(['name' => 'Lector', 'guard_name' => 'moonshine']);
    $two = Role::create(['name' => 'Editor', 'guard_name' => 'moonshine']);

    $this->deleteJson(roleResource()->getRoute('crud.massDelete'), [
        'ids' => [(string) $one->id, (string) $two->id],
    ])->assertOk();

    $this->assertDatabaseMissing('roles', ['id' => $one->id]);
    $this->assertDatabaseMissing('roles', ['id' => $two->id]);
    $this->assertDatabaseHas('roles', ['id' => User::SUPER_ADMIN_ROLE_ID]);
});
