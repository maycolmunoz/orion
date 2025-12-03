<?php

declare(strict_types=1);

namespace Modules\MoonLaunch\MoonShine\Resources\Admin\Pages;

use Modules\MoonLaunch\MoonShine\Resources\Admin\AdminResource;
use Modules\MoonLaunch\Traits\WithSoftDeletes;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends IndexPage<AdminResource>
 */
class AdminIndexPage extends IndexPage
{
    use WithSoftDeletes;

    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),

            //  Image::make('avatar')->translatable('moon-launch::ui.resource')
            //     ->modifyRawValue(fn(?string $raw): string => $raw ?? ''),

            Text::make('name')->translatable('moon-launch::ui.resource'),

            BelongsToMany::make('roles')->translatable('moon-launch::ui.resource')
                ->inLine(
                    separator: ' ',
                    badge: fn ($model, $value) => Badge::make((string) $value, 'primary'),
                ),

            Email::make('email')->translatable('moon-launch::ui.resource'),

            Date::make('created_at')->translatable('moon-launch::ui.resource')
                ->format('d/M/Y')
                ->sortable(),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons()
            ->prepend(
                ...$this->trashActions()
            );
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            BelongsToMany::make('roles')->translatable('moon-launch::ui.resource')
                ->selectMode(),
        ];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return $this->trashedTag();
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        return [];
    }

    /**
     * @param  TableBuilder  $component
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer(),
        ];
    }
}
