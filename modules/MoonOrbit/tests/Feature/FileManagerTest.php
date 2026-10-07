<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\MoonOrbit\Models\Media;
use Tests\TestCase;

require_once __DIR__.'/helpers.php';

final class StoringFailsFile extends UploadedFile
{
    public function store($path = '', $options = []): string|false
    {
        return false;
    }
}

/*
 * Este archivo vive fuera de tests/, así que tests/Pest.php no lo alcanza:
 * el TestCase de la app hay que vincularlo aquí.
 */
uses(TestCase::class, LazilyRefreshDatabase::class);

function fileManagerRoute(string $method): string
{
    return route('moonshine.method', [
        'pageUri' => 'file-manager-page',
        'method' => $method,
    ]);
}

it('lets a super admin open the file manager', function () {
    Storage::fake('public');
    makeMedia(['name' => 'report.pdf', 'path' => 'files/report.pdf']);
    loginAsSuperAdmin($this);

    $this->get(route('moonshine.page', 'file-manager-page'))
        ->assertOk()
        ->assertSee(__('moon-orbit::ui.file_manager.upload_title'))
        ->assertSee('report.pdf');
});

it('forbids a non super admin from opening the file manager', function () {
    loginAsUser($this);

    $this->get(route('moonshine.page', 'file-manager-page'))->assertForbidden();
});

it('stores uploaded files as media records', function () {
    Storage::fake('public');
    $user = loginAsSuperAdmin($this);

    $this->post(fileManagerRoute('uploadFiles'), [
        'files' => [
            UploadedFile::fake()->image('photo.png'),
            UploadedFile::fake()->create('report.pdf', 10, 'application/pdf'),
        ],
    ])->assertOk()
        ->assertJsonPath('redirect', route('moonshine.page', 'file-manager-page'));

    expect(Media::query()->count())->toBe(2);

    $media = Media::query()->where('name', 'photo.png')->firstOrFail();

    expect($media->uploader_id)->toBe($user->getKey())
        ->and($media->mime_type)->toStartWith('image/')
        ->and($media->size)->toBeGreaterThan(0);

    Storage::disk('public')->assertExists($media->path);
});

it('forbids a non super admin from uploading files', function () {
    Storage::fake('public');
    loginAsUser($this);

    $this->post(fileManagerRoute('uploadFiles'), [
        'files' => [UploadedFile::fake()->image('photo.png')],
    ])->assertForbidden();

    expect(Media::query()->count())->toBe(0);
});

it('skips a file whose store fails instead of persisting an empty path', function () {
    Storage::fake('public');
    loginAsSuperAdmin($this);

    $real = UploadedFile::fake()->image('broken.png');
    $file = new StoringFailsFile($real->getRealPath(), 'broken.png', $real->getMimeType(), null, true);

    $this->postJson(fileManagerRoute('uploadFiles'), [
        'files' => [$file],
    ])->assertOk();

    expect(Media::query()->count())->toBe(0);
});

it('rejects a disallowed extension', function () {
    Storage::fake('public');
    loginAsSuperAdmin($this);

    $this->postJson(fileManagerRoute('uploadFiles'), [
        'files' => [UploadedFile::fake()->create('script.php', 10, 'text/x-php')],
    ])->assertStatus(422);

    expect(Media::query()->count())->toBe(0);
});

it('rejects a file over the size limit', function () {
    Storage::fake('public');
    loginAsSuperAdmin($this);

    $this->postJson(fileManagerRoute('uploadFiles'), [
        'files' => [UploadedFile::fake()->create('big.pdf', 20000, 'application/pdf')],
    ])->assertStatus(422);

    expect(Media::query()->count())->toBe(0);
});

it('rejects more than ten files in a single upload', function () {
    Storage::fake('public');
    loginAsSuperAdmin($this);

    $this->postJson(fileManagerRoute('uploadFiles'), [
        'files' => collect(range(0, 10))
            ->map(fn (int $i): UploadedFile => UploadedFile::fake()->image("photo-{$i}.png"))
            ->all(),
    ])->assertStatus(422);

    expect(Media::query()->count())->toBe(0);
});

it('soft deletes a file and keeps it in the trash', function () {
    Storage::fake('public');
    $media = makeMedia();
    loginAsSuperAdmin($this);

    $this->post(fileManagerRoute('deleteMedia'), ['id' => $media->id])
        ->assertOk()
        ->assertJsonPath('redirect', route('moonshine.page', 'file-manager-page'));

    $this->assertSoftDeleted($media);
    Storage::disk('public')->assertExists($media->path);
});

it('forbids a non super admin from deleting a file', function () {
    Storage::fake('public');
    $media = makeMedia();
    loginAsUser($this);

    $this->post(fileManagerRoute('deleteMedia'), ['id' => $media->id])->assertForbidden();

    expect($media->fresh()->trashed())->toBeFalse();
});

it('rejects deleting a missing file', function () {
    Storage::fake('public');
    loginAsSuperAdmin($this);

    $this->postJson(fileManagerRoute('deleteMedia'), ['id' => 999])->assertStatus(422);
});

it('lists trashed files in the trash', function () {
    Storage::fake('public');
    $media = makeMedia(['name' => 'old.pdf', 'path' => 'files/old.pdf']);
    $media->delete();
    loginAsSuperAdmin($this);

    $this->get(route('moonshine.page', ['pageUri' => 'file-manager-page', 'trashed' => 1]))
        ->assertOk()
        ->assertSee(__('moon-orbit::ui.file_manager.trash'))
        ->assertSee('old.pdf');
});

it('paginates the media library', function () {
    Storage::fake('public');
    $time = now()->subDays(2);
    makeMedia(['name' => 'first.pdf', 'path' => 'files/first.pdf', 'created_at' => $time]);

    for ($i = 0; $i < 25; $i++) {
        makeMedia(['name' => "file-{$i}.pdf", 'path' => "files/file-{$i}.pdf", 'created_at' => $time->addHour()]);
    }

    loginAsSuperAdmin($this);

    $this->get(route('moonshine.page', 'file-manager-page'))
        ->assertOk()
        ->assertSee('file-24.pdf')
        ->assertDontSee('first.pdf');

    $this->get(route('moonshine.page', ['pageUri' => 'file-manager-page', 'page' => 2]))
        ->assertOk()
        ->assertSee('first.pdf')
        ->assertDontSee('file-24.pdf');
});

it('restores a trashed file', function () {
    Storage::fake('public');
    $media = makeMedia();
    $media->delete();
    loginAsSuperAdmin($this);

    $this->post(fileManagerRoute('restoreMedia'), ['id' => $media->id])
        ->assertOk()
        ->assertJsonPath('redirect', route('moonshine.page', ['pageUri' => 'file-manager-page', 'trashed' => 1]));

    expect($media->fresh()->trashed())->toBeFalse();
});

it('force deletes a trashed file and its physical file', function () {
    Storage::fake('public');
    $media = makeMedia();
    $media->delete();
    loginAsSuperAdmin($this);

    $this->post(fileManagerRoute('forceDeleteMedia'), ['id' => $media->id])->assertOk();

    $this->assertDatabaseMissing('media', ['id' => $media->id]);
    Storage::disk('public')->assertMissing($media->path);
});
