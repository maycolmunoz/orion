<?php

declare(strict_types=1);

namespace Modules\MoonLaunch\MoonShine\Resources\Admin;

use Modules\MoonLaunch\Models\User;
use Modules\MoonLaunch\MoonShine\Resources\Admin\Pages\AdminDetailPage;
use Modules\MoonLaunch\MoonShine\Resources\Admin\Pages\AdminFormPage;
use Modules\MoonLaunch\MoonShine\Resources\Admin\Pages\AdminIndexPage;
use Modules\MoonLaunch\Traits\WithProperties;
use Modules\MoonLaunch\Traits\WithTrashedQuery;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Attributes\Icon;
use Sweet1s\MoonshineRBAC\Traits\WithRoleFormComponent;
use Sweet1s\MoonshineRBAC\Traits\WithRolePermissions;

#[Icon('s.user-group')]
/**
 * @extends ModelResource<User, AdminIndexPage, AdminFormPage, AdminDetailPage>
 */
class AdminResource extends ModelResource
{
    use WithProperties;
    use WithRoleFormComponent;
    use WithRolePermissions;
    use WithTrashedQuery;

    protected string $model = User::class;

    public function __construct()
    {
        $this->title(__('moon-launch::ui.resource.admins_title'))
            ->with(['roles'])
            ->column('name');
    }

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            AdminIndexPage::class,
            AdminFormPage::class,
            AdminDetailPage::class,
        ];
    }

    protected function search(): array
    {
        return ['id', 'name', 'email'];
    }
}
