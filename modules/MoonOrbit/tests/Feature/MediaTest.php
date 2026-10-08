<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Modules\MoonLaunch\Models\User;
use Modules\MoonOrbit\Models\ActivityLog;
use Modules\MoonOrbit\Models\Media;
use Tests\TestCase;

require_once __DIR__.'/helpers.php';

if (! moonOrbitIsActive()) {
    return;
}

uses(TestCase::class, LazilyRefreshDatabase::class);

it('resolves the public url of a media file', function () {
    Storage::fake('public');
    $media = makeMedia(['path' => 'files/cat.png']);

    expect($media->url())->toBe(Storage::disk('public')->url('files/cat.png'));
});

it('detects image media by extension', function () {
    Storage::fake('public');
    $image = makeMedia(['path' => 'files/cat.png']);
    $document = makeMedia(['path' => 'files/report.pdf']);

    expect($image->isImage())->toBeTrue()
        ->and($document->isImage())->toBeFalse();
});

it('belongs to an uploader', function () {
    Storage::fake('public');
    $user = User::create([
        'name' => 'Uploader',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);
    $media = makeMedia(['uploader_id' => $user->getKey()]);

    expect($media->uploader?->getKey())->toBe($user->getKey());
});

it('registers untracked disk files via media:sync, idempotently', function () {
    Storage::fake('public');
    makeMedia(['name' => 'a.pdf', 'path' => 'files/a.pdf']);
    Storage::disk('public')->put('files/b.pdf', 'content');

    expect(Artisan::call('media:sync'))->toBe(0);

    expect(Media::query()->pluck('path')->all())
        ->toContain('files/a.pdf', 'files/b.pdf')
        ->and(Media::query()->count())->toBe(2);

    expect(Artisan::call('media:sync'))->toBe(0)
        ->and(Media::query()->count())->toBe(2);
});

it('registers files in subdirectories via media:sync', function () {
    Storage::fake('public');
    Storage::disk('public')->put('files/publications/deep.pdf', 'content');

    expect(Artisan::call('media:sync'))->toBe(0);

    expect(Media::query()->pluck('path')->all())->toContain('files/publications/deep.pdf');
});

it('reports media rows whose file is missing on disk with --check', function () {
    Storage::fake('public');
    Media::query()->create([
        'name' => 'gone.pdf',
        'disk' => Media::DISK,
        'path' => 'files/gone.pdf',
    ]);

    expect(Artisan::call('media:sync', ['--check' => true]))->toBe(1);

    $output = Artisan::output();

    expect($output)->toContain('gone.pdf');
});

it('passes --check when every media file exists on disk', function () {
    Storage::fake('public');
    makeMedia(['name' => 'here.pdf', 'path' => 'files/here.pdf']);
    Storage::disk('public')->put('files/here.pdf', 'content');

    expect(Artisan::call('media:sync', ['--check' => true]))->toBe(0);
});

it('records its lifecycle in the activity log', function () {
    Storage::fake('public');
    $subjectType = (new Media)->getMorphClass();

    $media = makeMedia();
    $media->delete();
    $media->restore();

    $events = ActivityLog::query()
        ->where('subject_type', $subjectType)
        ->where('subject_id', $media->getKey())
        ->pluck('event')
        ->all();

    expect($events)->toContain('created', 'deleted', 'restored')
        ->and($events)->not->toContain('updated');
});
