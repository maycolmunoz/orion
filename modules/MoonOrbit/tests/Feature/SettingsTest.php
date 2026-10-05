<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\MoonOrbit\Models\Setting;
use MoonShine\ColorManager\Palettes\CyanPalette;
use Tests\TestCase;

require_once __DIR__.'/helpers.php';

/*
 * Este archivo vive fuera de tests/, así que tests/Pest.php no lo alcanza:
 * el TestCase de la app hay que vincularlo aquí.
 */
uses(TestCase::class, LazilyRefreshDatabase::class);

it('falls back to the default when a setting is missing', function () {
    expect(Setting::get('app_name'))->toBeNull()
        ->and(Setting::get('app_name', 'Orion'))->toBe('Orion');
});

it('stores settings and invalidates the cache', function () {
    Setting::values();

    Setting::put(['app_name' => 'Orion', 'logo' => 'moonshine/logo/logo.png']);

    expect(Setting::get('app_name'))->toBe('Orion')
        ->and(Setting::get('logo'))->toBe('moonshine/logo/logo.png')
        ->and(Setting::values())->toBe([
            'app_name' => 'Orion',
            'logo' => 'moonshine/logo/logo.png',
        ]);
});

it('overwrites an existing key instead of duplicating it', function () {
    Setting::put(['app_name' => 'Orion']);
    Setting::put(['app_name' => 'Orbit']);

    expect(Setting::get('app_name'))->toBe('Orbit')
        ->and(Setting::query()->where('key', 'app_name')->count())->toBe(1);
});

it('allows clearing a value', function () {
    Setting::put(['app_name' => 'Orion']);
    Setting::put(['app_name' => null]);

    expect(Setting::get('app_name'))->toBeNull();
});

it('lets a super admin open the settings page', function () {
    Setting::put(['app_name' => 'Orion']);
    loginAsSuperAdmin($this);

    $this->get(route('moonshine.page', 'settings-page'))
        ->assertOk()
        ->assertSee(__('moon-orbit::ui.settings.branding'))
        ->assertSee('value="Orion"', false);
});

it('forbids a non super admin from opening the settings page', function () {
    loginAsUser($this);

    $this->get(route('moonshine.page', 'settings-page'))->assertForbidden();
});

it('forbids a non super admin from saving settings', function () {
    loginAsUser($this);

    $this->post(route('moonshine.method', [
        'pageUri' => 'settings-page',
        'method' => 'saveSettings',
    ]), ['app_name' => 'Hijacked'])->assertForbidden();

    expect(Setting::get('app_name'))->toBeNull();
});

it('saves settings for a super admin', function () {
    loginAsSuperAdmin($this);

    $this->post(route('moonshine.method', [
        'pageUri' => 'settings-page',
        'method' => 'saveSettings',
    ]), [
        'app_name' => 'Orbit',
        'palette' => CyanPalette::class,
        'layout' => 'topbar',
    ])->assertOk()
        ->assertJsonPath('redirect', route('moonshine.page', 'settings-page'));

    expect(Setting::get('app_name'))->toBe('Orbit')
        ->and(Setting::get('palette'))->toBe(CyanPalette::class)
        ->and(Setting::get('layout'))->toBe('topbar');
});

it('rejects an unknown layout', function () {
    loginAsSuperAdmin($this);

    $this->postJson(route('moonshine.method', [
        'pageUri' => 'settings-page',
        'method' => 'saveSettings',
    ]), [
        'app_name' => 'Orbit',
        'layout' => 'floating',
    ])->assertStatus(422);

    expect(Setting::get('layout'))->toBeNull();
});

it('rejects an unknown palette', function () {
    loginAsSuperAdmin($this);

    $this->postJson(route('moonshine.method', [
        'pageUri' => 'settings-page',
        'method' => 'saveSettings',
    ]), [
        'app_name' => 'Orbit',
        'palette' => 'App\\MoonShine\\Palettes\\EvilPalette',
    ])->assertStatus(422);

    expect(Setting::get('palette'))->toBeNull();
});

it('stores an uploaded logo on the public disk', function () {
    Storage::fake('public');
    loginAsSuperAdmin($this);

    $this->post(route('moonshine.method', [
        'pageUri' => 'settings-page',
        'method' => 'saveSettings',
    ]), [
        'app_name' => 'Orbit',
        'logo' => UploadedFile::fake()->image('logo.png'),
    ])->assertOk();

    Storage::disk('public')->assertExists(Setting::get('logo'));
});

it('deletes the previous logo file when a new one is uploaded', function () {
    Storage::fake('public');
    Storage::disk('public')->put('moonshine/logo/old.png', 'old');
    Setting::put(['logo' => 'moonshine/logo/old.png']);
    loginAsSuperAdmin($this);

    $this->post(route('moonshine.method', [
        'pageUri' => 'settings-page',
        'method' => 'saveSettings',
    ]), [
        'app_name' => 'Orbit',
        'logo' => UploadedFile::fake()->image('new.png'),
    ])->assertOk();

    Storage::disk('public')->assertMissing('moonshine/logo/old.png')
        ->assertExists(Setting::get('logo'));
});

it('deletes the logo file when the logo is cleared', function () {
    Storage::fake('public');
    Storage::disk('public')->put('moonshine/logo/removed.png', 'old');
    Setting::put(['logo' => 'moonshine/logo/removed.png']);
    loginAsSuperAdmin($this);

    $this->post(route('moonshine.method', [
        'pageUri' => 'settings-page',
        'method' => 'saveSettings',
    ]), ['app_name' => 'Orbit'])->assertOk();

    Storage::disk('public')->assertMissing('moonshine/logo/removed.png');
    expect(Setting::get('logo'))->toBeNull();
});

it('keeps the current logo when the field is resubmitted untouched', function () {
    Setting::put(['logo' => 'moonshine/logo/kept.png']);
    loginAsSuperAdmin($this);

    $this->post(route('moonshine.method', [
        'pageUri' => 'settings-page',
        'method' => 'saveSettings',
    ]), [
        'app_name' => 'Orbit',
        'hidden_logo' => 'moonshine/logo/kept.png',
    ])->assertOk();

    expect(Setting::get('logo'))->toBe('moonshine/logo/kept.png');
});

it('never persists the hidden_logo form helper as a setting', function () {
    Setting::put(['logo' => 'moonshine/logo/current.png']);
    loginAsSuperAdmin($this);

    $this->post(route('moonshine.method', [
        'pageUri' => 'settings-page',
        'method' => 'saveSettings',
    ]), [
        'app_name' => 'Orbit',
        'hidden_logo' => 'moonshine/logo/current.png',
    ])->assertOk();

    expect(Setting::values())->not->toHaveKey('hidden_logo')
        ->and(Setting::query()->where('key', 'hidden_logo')->exists())->toBeFalse();
});
