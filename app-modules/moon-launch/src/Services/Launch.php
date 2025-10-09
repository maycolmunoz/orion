<?php

namespace Modules\MoonLaunch\Services;

use Modules\MoonLaunch\MoonShine\Resources\AdminResource;
use Modules\MoonLaunch\MoonShine\Resources\RoleResource;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use Sweet1s\MoonshineRBAC\Components\MenuRBAC;
use Sweet1s\MoonshineRBAC\Resource\PermissionResource;

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
            PermissionResource::class,
        ];
    }

    /**
     * getMenu
     */
    public function getMenu(): array
    {
        return MenuRBAC::menu(
            MenuGroup::make('system', [
                MenuItem::make('admins_title', AdminResource::class)
                    ->translatable('moon-launch::ui.resource'),

                MenuItem::make('roles', RoleResource::class)
                    ->translatable('moon-launch::ui.resource'),

                MenuItem::make('permissions', PermissionResource::class)
                    ->translatable('moonshine-rbac::ui')
                    ->icon('s.shield-check'),
            ])
                ->translatable('moonshine::ui.resource')
                ->icon('m.cube')
        );
    }
}
