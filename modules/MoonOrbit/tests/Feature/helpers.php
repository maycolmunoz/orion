<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\Models\User;
use Modules\MoonOrbit\Models\Media;
use Modules\MoonOrbit\Providers\MoonOrbitServiceProvider;
use Tests\TestCase;

function loginAsUser(TestCase $test): User
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
        'password' => 'password',
    ]);

    $test->be($user, 'moonshine');

    return $user;
}

function loginAsSuperAdmin(TestCase $test): User
{
    $user = loginAsUser($test);

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

function moonOrbitIsActive(): bool
{
    /** @var array<int, class-string> $providers */
    $providers = require dirname(__DIR__, 4).'/bootstrap/providers.php';

    return in_array(MoonOrbitServiceProvider::class, $providers, true);
}
