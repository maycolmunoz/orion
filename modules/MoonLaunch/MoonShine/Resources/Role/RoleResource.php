<?php

declare(strict_types=1);

namespace Modules\MoonLaunch\MoonShine\Resources\Role;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\Models\User;
use Modules\MoonLaunch\MoonShine\Resources\Role\Pages\RoleDetailPage;
use Modules\MoonLaunch\MoonShine\Resources\Role\Pages\RoleFormPage;
use Modules\MoonLaunch\MoonShine\Resources\Role\Pages\RoleIndexPage;
use Modules\MoonLaunch\Traits\WithProperties;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\Enums\PageType;
use MoonShine\Support\ListOf;
use RuntimeException;
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

    public function delete(DataWrapperContract $item, ?FieldsContract $fields = null): bool
    {
        if ((int) $item->getOriginal()->getKey() === User::SUPER_ADMIN_ROLE_ID) {
            throw new RuntimeException(__('moon-launch::ui.resource.super_admin_protected'));
        }

        return parent::delete($item, $fields);
    }

    /**
     * @param  array<int|string>  $ids
     */
    public function massDelete(array $ids): void
    {
        $ids = array_map('strval', $ids);

        if (in_array((string) User::SUPER_ADMIN_ROLE_ID, $ids, true)) {
            throw new RuntimeException(__('moon-launch::ui.resource.super_admin_protected'));
        }

        parent::massDelete($ids);
    }
}
