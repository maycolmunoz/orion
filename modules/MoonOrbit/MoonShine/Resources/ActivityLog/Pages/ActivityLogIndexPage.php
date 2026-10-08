<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\MoonShine\Resources\ActivityLog\Pages;

use Modules\MoonOrbit\Models\ActivityLog;
use Modules\MoonOrbit\MoonShine\Resources\ActivityLog\ActivityLogResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Support\Enums\Color;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\Field;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<ActivityLogResource>
 */
class ActivityLogIndexPage extends IndexPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Text::make('user_name')->translatable('moon-orbit::ui.activity_log')
                ->changePreview(static fn (mixed $value): string => filled($value) ? e((string) $value) : __('moon-orbit::ui.activity_log.system'))
                ->sortable(),
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
                )->render())
                ->sortable(),
            Text::make('subject_type')->translatable('moon-orbit::ui.activity_log')
                ->changePreview(static fn (mixed $value, Field $field): string => ActivityLogResource::subjectPreview($field->getData()?->getOriginal())),
            Date::make('created_at')->translatable('moon-orbit::ui.activity_log')
                ->format('d/m/Y H:i')
                ->sortable(),
        ];
    }

    protected function topRightButtons(): ListOf
    {
        return parent::topRightButtons()->add(
            ActionButton::make(__('moon-orbit::ui.activity_log.clear_all'))
                ->method('clearAll', page: $this, resource: $this->getResource())
                ->withConfirm(
                    title: __('moon-orbit::ui.activity_log.clear_all_confirm'),
                    button: __('moon-orbit::ui.activity_log.clear_all'),
                )
                ->error(),
        );
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            Select::make('user_id')->translatable('moon-orbit::ui.activity_log')
                ->options(static fn (): array => config('moonshine.auth.model')::withTrashed()->pluck('name', 'id')->all()),
            Select::make('event')->translatable('moon-orbit::ui.activity_log')
                ->options(static fn (): array => collect(['created', 'updated', 'deleted', 'forceDeleted', 'restored'])
                    ->mapWithKeys(static fn (string $event): array => [
                        $event => __("moon-orbit::ui.activity_log.events.$event"),
                    ])
                    ->all()),
            Select::make('subject_type')->translatable('moon-orbit::ui.activity_log')
                ->options(static fn (): array => ActivityLog::query()
                    ->whereNotNull('subject_type')
                    ->distinct()
                    ->pluck('subject_type', 'subject_type')
                    ->mapWithKeys(static fn (string $type): array => [$type => ActivityLogResource::modelName($type) ?? $type])
                    ->all()),
            DateRange::make('created_at')->translatable('moon-orbit::ui.activity_log'),
        ];
    }
}
