<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\Models\User;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('skips creating a super admin when one already exists', function () {
    Role::forceCreate([
        'id' => User::SUPER_ADMIN_ROLE_ID,
        'name' => 'Super Admin',
        'guard_name' => 'moonshine',
    ]);

    $user = User::create([
        'name' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);

    $user->assignRole('Super Admin');

    expect(Artisan::call('launch:install'))->toBe(0)
        ->and(Artisan::output())->toContain('Super Admin user already exists');
});
