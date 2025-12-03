<?php

namespace Modules\MoonLaunch\Services;

use Modules\MoonLaunch\MoonShine\Resources\Admin\AdminResource;
use Modules\MoonLaunch\MoonShine\Resources\Role\RoleResource;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use Sweet1s\MoonshineRBAC\Components\MenuRBAC;

class Launch
{
    /**
     * getResources
     */
    public function getResources(): array
    {
        return [
            AdminResource::class,
            RoleResource::class,
        ];
    }

    /**
     * getMenu
     */
    public function getMenu(): array
    {
        return
        MenuRBAC::menu(
            MenuGroup::make('system', [
                MenuItem::make(AdminResource::class, 'admins_title')
                    ->translatable('moon-launch::ui.resource'),

                MenuItem::make(RoleResource::class, 'roles')
                    ->translatable('moon-launch::ui.resource'),
            ])
                ->translatable('moonshine::ui.resource')
                ->icon('m.cube')
        );
    }
}
