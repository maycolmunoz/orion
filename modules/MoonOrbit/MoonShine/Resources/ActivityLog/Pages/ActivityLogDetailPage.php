<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\MoonShine\Resources\ActivityLog\Pages;

use Modules\MoonOrbit\MoonShine\Resources\ActivityLog\ActivityLogResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Support\Enums\Color;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Field;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<ActivityLogResource>
 */
class ActivityLogDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Text::make('user_name')->translatable('moon-orbit::ui.activity_log')
                ->changePreview(static fn (mixed $value): string => filled($value) ? e((string) $value) : __('moon-orbit::ui.activity_log.system')),
            Text::make('event')->translatable('moon-orbit::ui.activity_log')
                ->changePreview(static fn (mixed $value): string => (string) Badge::make(
                    __("moon-orbit::ui.activity_log.events.$value"),
                    match ($value) {
                        'created' => Color::GREEN,
                        'updated' => Color::BLUE,
                        'deleted', 'forceDeleted' => Color::RED,
                        'restored' => Color::YELLOW,
                        default => Color::GRAY,
                    },
                )->render()),
            Text::make('subject_type')->translatable('moon-orbit::ui.activity_log')
                ->changePreview(static fn (mixed $value, Field $field): string => ActivityLogResource::subjectPreview($field->getData()?->getOriginal())),
            Date::make('created_at')->translatable('moon-orbit::ui.activity_log')
                ->format('d/m/Y H:i'),
            Preview::make('changes')->translatable('moon-orbit::ui.activity_log')
                ->changePreview(static fn (mixed $value): string => self::formatChanges(is_array($value) ? $value : [])),
        ];
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private static function formatChanges(array $changes): string
    {
        $attributes = is_array($changes['attributes'] ?? null) ? $changes['attributes'] : [];
        $old = is_array($changes['old'] ?? null) ? $changes['old'] : [];

        $rows = [];

        foreach ($attributes as $key => $value) {
            $rows[] = array_key_exists($key, $old)
                ? sprintf('%s: %s → %s', $key, self::stringify($old[$key]), self::stringify($value))
                : sprintf('%s: %s', $key, self::stringify($value));
        }

        foreach ($old as $key => $value) {
            if (! array_key_exists($key, $attributes)) {
                $rows[] = sprintf('%s: %s', $key, self::stringify($value));
            }
        }

        if ($rows === []) {
            return '—';
        }

        return '<pre class="whitespace-pre-wrap text-sm">'.e(implode(PHP_EOL, $rows)).'</pre>';
    }

    private static function stringify(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            $value === null => 'null',
            is_array($value) => (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            default => (string) $value,
        };
    }
}
