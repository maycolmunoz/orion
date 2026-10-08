<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\MoonShine\Fields;

use Modules\MoonOrbit\Models\Media;
use MoonShine\UI\Components\Thumbnails;
use MoonShine\UI\Fields\Select;

/**
 * Selector de un archivo de la biblioteca de medios.
 *
 * Guarda el id del Media seleccionado en la columna del campo; el consumidor
 * resuelve el archivo con Media::find($id)?->url().
 *
 * En modo vista (index/detail) muestra la miniatura si es imagen, o el nombre.
 */
final class MediaField extends Select
{
    protected string $view = 'moonshine::fields.select';

    protected function booted(): void
    {
        parent::booted();

        $this
            ->options(static fn (): array => Media::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all())
            ->searchable()
            ->nullable()
            ->changePreview(static fn (mixed $id): string => self::mediaPreview($id));
    }

    private static function mediaPreview(mixed $id): string
    {
        $media = Media::withTrashed()->find($id);

        if ($media === null) {
            return '—';
        }

        return $media->isImage()
            ? (string) Thumbnails::make($media->url())->render()
            : e($media->name);
    }
}
