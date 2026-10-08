<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\MoonOrbit\Models\Media;
use Throwable;

class MediaSync extends Command
{
    protected $signature = 'media:sync {--check : List media rows whose file is missing on disk}';

    protected $description = 'Register files already present on the public disk into the media library';

    public function handle(): int
    {
        if ($this->option('check')) {
            return $this->checkIntegrity();
        }

        return $this->registerFiles();
    }

    private function registerFiles(): int
    {
        $disk = Storage::disk(Media::DISK);
        $known = Media::withTrashed()->pluck('path')->all();
        $added = 0;
        $failed = 0;

        foreach ($disk->allFiles(Media::DIR) as $path) {
            if (in_array($path, $known, true)) {
                continue;
            }

            try {
                Media::query()->create([
                    'name' => basename($path),
                    'disk' => Media::DISK,
                    'path' => $path,
                    'mime_type' => $disk->mimeType($path) ?: null,
                    'size' => $disk->size($path),
                ]);

                $added++;
            } catch (Throwable $e) {
                $failed++;
                $this->error("Failed to register {$path}: {$e->getMessage()}");
            }
        }

        $this->info("Registered {$added} file(s).");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function checkIntegrity(): int
    {
        $disk = Storage::disk(Media::DISK);

        $missing = Media::withTrashed()
            ->get()
            ->filter(fn (Media $media): bool => ! $disk->exists($media->path));

        if ($missing->isEmpty()) {
            $this->info('All media files exist on disk.');

            return self::SUCCESS;
        }

        foreach ($missing as $media) {
            $this->error("#{$media->getKey()} {$media->name}: missing file {$media->path}");
        }

        return self::FAILURE;
    }
}
