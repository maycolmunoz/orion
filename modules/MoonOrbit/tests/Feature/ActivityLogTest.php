<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\Models\User;
use Modules\MoonOrbit\Models\ActivityLog;
use Modules\MoonOrbit\Models\Setting;
use Modules\MoonOrbit\MoonShine\Resources\ActivityLog\ActivityLogResource;
use MoonShine\Contracts\Core\DependencyInjection\RouterContract;
use Tests\TestCase;

require_once __DIR__.'/helpers.php';

if (! moonOrbitIsActive()) {
    return;
}

uses(TestCase::class, LazilyRefreshDatabase::class);

function activityLogResource(): ActivityLogResource
{
    return app(ActivityLogResource::class);
}

function activityMethodUrl(string $method): string
{
    $resource = app(ActivityLogResource::class);

    return app(RouterContract::class)->getEndpoints()->method(
        method: $method,
        page: $resource->getIndexPage(),
        resource: $resource,
    );
}

it('records user creation with the acting admin as causer', function () {
    $admin = loginAsSuperAdmin($this);

    $user = User::create([
        'name' => 'Created User',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'secret',
    ]);

    $log = ActivityLog::query()
        ->where('subject_type', $user->getMorphClass())
        ->where('subject_id', $user->id)
        ->where('event', 'created')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->user_name)->toBe($admin->name)
        ->and($log->subject_label)->toBe('Created User')
        ->and($log->changes['attributes']['name'])->toBe('Created User');
});

it('never records sensitive attributes', function () {
    loginAsSuperAdmin($this);

    $user = User::create([
        'name' => 'Safe User',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'secret',
    ]);

    $log = ActivityLog::query()->where('subject_id', $user->id)->where('event', 'created')->first();

    expect($log->changes['attributes'])->not->toHaveKey('password')
        ->and(json_encode($log->changes))->not->toContain('secret');
});

it('records updates with old and new values', function () {
    loginAsSuperAdmin($this);

    $user = User::create([
        'name' => 'Original Name',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'secret',
    ]);

    $user->update(['name' => 'Renamed']);

    $log = ActivityLog::query()
        ->where('subject_id', $user->id)
        ->where('event', 'updated')
        ->first();

    expect($log->changes['attributes']['name'])->toBe('Renamed')
        ->and($log->changes['old']['name'])->toBe('Original Name')
        ->and($log->changes['attributes'])->not->toHaveKey('updated_at');
});

it('records deletions and restorations', function () {
    loginAsSuperAdmin($this);

    $user = User::create([
        'name' => 'Deleted User',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'secret',
    ]);

    $user->delete();
    $user->restore();

    $deleted = ActivityLog::query()->where('subject_id', $user->id)->where('event', 'deleted')->first();
    $restored = ActivityLog::query()->where('subject_id', $user->id)->where('event', 'restored')->first();

    expect($deleted->changes['old']['name'])->toBe('Deleted User')
        ->and($restored->changes['attributes']['name'])->toBe('Deleted User');
});

it('records force deletes without duplicating the soft delete', function () {
    loginAsSuperAdmin($this);

    $user = User::create([
        'name' => 'Force Deleted User',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'secret',
    ]);

    $user->forceDelete();

    $events = ActivityLog::query()->where('subject_id', $user->id)->pluck('event')->all();

    expect($events)->toContain('forceDeleted')
        ->and($events)->not->toContain('deleted');
});

it('records settings changes', function () {
    loginAsSuperAdmin($this);

    Setting::put(['app_name' => 'Orion']);

    $log = ActivityLog::query()
        ->where('subject_type', Setting::class)
        ->where('event', 'created')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->subject_id)->toBe('app_name')
        ->and($log->subject_label)->toBe('app_name')
        ->and($log->changes['attributes']['value'])->toBe('Orion');
});

it('shows the activity log to a super admin', function () {
    $admin = loginAsSuperAdmin($this);
    $admin->update(['name' => 'Audited Admin']);

    $this->get(activityLogResource()->getIndexPageUrl())
        ->assertOk()
        ->assertSee('Audited Admin')
        ->assertSee(__('moon-orbit::ui.activity_log.models.User').' · Audited Admin')
        ->assertSee('activity-log-resource-detail-modal')
        ->assertSee(__('moon-orbit::ui.activity_log.title'))
        ->assertSee(__('moon-orbit::ui.activity_log.events.updated'));
});

it('deletes an activity log record', function () {
    loginAsSuperAdmin($this);

    $user = User::create([
        'name' => 'Deletable User',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'secret',
    ]);

    $log = ActivityLog::query()
        ->where('subject_id', $user->id)
        ->where('event', 'created')
        ->firstOrFail();

    $this->delete(activityLogResource()->getRoute('crud.destroy', $log->id))->assertRedirect();

    expect(ActivityLog::query()->find($log->id))->toBeNull();
});

it('forbids deleting an activity log without permission', function () {
    loginAsUser($this);

    $log = ActivityLog::query()->firstOrFail();

    $this->delete(activityLogResource()->getRoute('crud.destroy', $log->id))->assertForbidden();

    expect(ActivityLog::query()->find($log->id))->not->toBeNull();
});

it('forbids a non super admin from opening the activity log', function () {
    loginAsUser($this);

    $this->get(activityLogResource()->getIndexPageUrl())->assertForbidden();
});

it('shows the full change diff on the detail page', function () {
    loginAsSuperAdmin($this);

    $longName = str_repeat('A', 120);

    $user = User::create([
        'name' => $longName,
        'email' => fake()->unique()->safeEmail(),
        'password' => 'secret',
    ]);

    $user->update(['name' => 'Short']);

    $log = ActivityLog::query()
        ->where('subject_id', $user->id)
        ->where('event', 'updated')
        ->first();

    $this->get(activityLogResource()->getDetailPageUrl($log->id))
        ->assertOk()
        ->assertSee($longName)
        ->assertSee('Short');
});

it('filters activities by event', function () {
    loginAsSuperAdmin($this);

    Role::create(['name' => 'Filtered Role', 'guard_name' => 'moonshine']);

    $created = activityLogResource()->getIndexPageUrl(['filter' => ['event' => 'created']]);
    $deleted = activityLogResource()->getIndexPageUrl(['filter' => ['event' => 'deleted']]);

    $this->get($created)->assertOk()->assertSee('Filtered Role');
    $this->get($deleted)->assertOk()->assertDontSee('Filtered Role');
});

it('filters activities by user', function () {
    $admin = loginAsSuperAdmin($this);

    $other = loginAsUser($this);
    Role::create(['name' => 'Filtered By User Role', 'guard_name' => 'moonshine']);

    $this->be($admin, 'moonshine');

    $otherLogs = activityLogResource()->getIndexPageUrl(['filter' => ['user_id' => $other->id]]);
    $adminLogs = activityLogResource()->getIndexPageUrl(['filter' => ['user_id' => $admin->id]]);

    $this->get($otherLogs)->assertOk()->assertSee('Filtered By User Role');
    $this->get($adminLogs)->assertOk()->assertDontSee('Filtered By User Role');
});

it('prunes activity logs older than the retention window', function () {
    loginAsSuperAdmin($this);

    $user = User::create([
        'name' => 'Pruned User',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'secret',
    ]);

    ActivityLog::query()->update(['created_at' => now()->subDays(120)]);

    expect(Artisan::call('orbit:activity:prune', ['--days' => 90]))->toBe(0);

    expect(ActivityLog::query()->count())->toBe(0);

    $user->delete();

    expect(ActivityLog::query()->count())->toBe(1);

    expect(Artisan::call('orbit:activity:prune'))->toBe(0)
        ->and(ActivityLog::query()->count())->toBe(1);
});

it('clears the whole activity log via the clear button method', function () {
    loginAsSuperAdmin($this);

    User::create([
        'name' => 'Clearable User',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'secret',
    ]);

    expect(ActivityLog::query()->count())->toBeGreaterThan(0);

    $this->post(activityMethodUrl('clearAll'))->assertOk();

    expect(ActivityLog::query()->count())->toBe(0);
});

it('forbids clearing the activity log without permission', function () {
    loginAsUser($this);

    $this->post(activityMethodUrl('clearAll'))->assertForbidden();

    expect(ActivityLog::query()->count())->not->toBe(0);
});

it('shows the clear button to a super admin', function () {
    loginAsSuperAdmin($this);

    $this->get(activityLogResource()->getIndexPageUrl())
        ->assertOk()
        ->assertSee(__('moon-orbit::ui.activity_log.clear_all'));
});
