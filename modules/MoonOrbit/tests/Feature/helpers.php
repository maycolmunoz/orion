<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\Models\User;
use Modules\MoonOrbit\Models\Media;

function loginAsAdmin(): User
{
    Role::query()
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
        'password' => Hash::make('password'),
    ]);

    Auth::guard('moonshine')->setUser($user);

    return $user;
}

function loginAsSuperAdmin(): User
{
    $user = loginAsAdmin();

    $user->assignRole('Super Admin');

    return $user;
}

/**
 * @param  array<string, mixed>  $attributes
 */
function makeMedia(array $attributes = []): Media
{
    $media = Media::query()->create(array_merge([
        'name' => 'report.pdf',
        'disk' => 'public',
        'path' => 'files/report.pdf',
        'mime_type' => 'application/pdf',
        'size' => 10,
    ], $attributes));

    Storage::disk($media->disk)->put($media->path, 'content');

    return $media;
}
