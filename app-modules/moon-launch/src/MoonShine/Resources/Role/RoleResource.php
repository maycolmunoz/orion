<?php

declare(strict_types=1);

namespace Modules\MoonLaunch\MoonShine\Resources\Role;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\MoonShine\Resources\Role\Pages\RoleDetailPage;
use Modules\MoonLaunch\MoonShine\Resources\Role\Pages\RoleFormPage;
use Modules\MoonLaunch\MoonShine\Resources\Role\Pages\RoleIndexPage;
use Modules\MoonLaunch\Traits\WithProperties;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\Enums\PageType;
use MoonShine\Support\ListOf;
use Sweet1s\MoonshineRBAC\Traits\WithPermissionsFormComponent;
use Sweet1s\MoonshineRBAC\Traits\WithRolePermissions;

#[Icon('s.rectangle-group')]
/**
 * @extends ModelResource<Role, RoleIndexPage, RoleFormPage, RoleDetailPage>
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

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            RoleIndexPage::class,
            RoleFormPage::class,
            RoleDetailPage::class,
        ];
    }

    protected function search(): array
    {
        return ['id', 'name'];
    }

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        return $builder->withCount('permissions');
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::VIEW);
    }
}
