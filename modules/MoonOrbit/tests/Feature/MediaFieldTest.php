<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\MoonOrbit\MoonShine\Fields\MediaField;
use Tests\TestCase;

require_once __DIR__.'/helpers.php';

uses(TestCase::class, LazilyRefreshDatabase::class);

it('lists the media library as selectable options', function () {
    Storage::fake('public');
    $first = makeMedia(['name' => 'cat.png', 'path' => 'files/cat.png']);
    $second = makeMedia(['name' => 'report.pdf', 'path' => 'files/report.pdf']);

    $options = MediaField::make('File', 'media_id')
        ->getValues()
        ->toRaw()['options'];

    expect($options)->toBe([
        (string) $first->id => 'cat.png',
        (string) $second->id => 'report.pdf',
    ]);
});
