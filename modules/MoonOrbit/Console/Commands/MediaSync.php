<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\MoonOrbit\Models\Media;

class MediaSync extends Command
{
    protected $signature = 'media:sync';

    protected $description = 'Register files already present on the public disk into the media library';

    public function handle(): int
    {
        $disk = Storage::disk(Media::DISK);
        $known = Media::withTrashed()->pluck('path')->all();
        $added = 0;

        foreach ($disk->files(Media::DIR) as $path) {
            if (in_array($path, $known, true)) {
                continue;
            }

            Media::query()->create([
                'name' => basename($path),
                'disk' => Media::DISK,
                'path' => $path,
                'mime_type' => $disk->mimeType($path) ?: null,
                'size' => $disk->size($path),
            ]);

            $added++;
        }

        $this->info("Registered {$added} file(s).");

        return self::SUCCESS;
    }
}
