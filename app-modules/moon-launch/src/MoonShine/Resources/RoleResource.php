<?php

declare(strict_types=1);

namespace Modules\MoonLaunch\MoonShine\Resources;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\Traits\WithProperties;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\PageType;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Text;
use Sweet1s\MoonshineRBAC\Traits\WithPermissionsFormComponent;
use Sweet1s\MoonshineRBAC\Traits\WithRolePermissions;

#[Icon('s.rectangle-group')]
/**
 * @extends ModelResource<Role>
 */
class RoleResource extends ModelResource
{
    use WithPermissionsFormComponent;
    use WithProperties;
    use WithRolePermissions;

    protected string $model = Role::class;

    public function __construct()
    {
        $this->title(__('moon-launch::ui.resource.roles'))
            ->redirectAfterSave(PageType::FORM)
            ->itemsPerPage(20)
            ->column('name');
    }

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        return $builder->withCount('permissions');
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::VIEW);
    }

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            Text::make('name')->translatable('moon-launch::ui.resource')
                ->sortable(),
            Text::make('permissions', 'permissions_count')
                ->translatable('moon-launch::ui.resource')
                ->sortable(),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                Text::make('name')->translatable('moon-launch::ui.resource')
                    ->required(),
            ]),
        ];
    }

    // /**
    //  * @return array{name: array|string}
    //  */
    protected function rules($item): array
    {
        return [
            'name' => [
                'required',
                'min:5',
                Rule::unique('roles', 'name')->ignore($item?->id),
            ],
        ];
    }

    protected function search(): array
    {
        return [
            'id',
            'name',
        ];
    }
}
