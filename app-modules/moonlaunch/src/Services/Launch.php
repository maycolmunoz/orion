<?php

namespace Modules\Moonlaunch\Services;

use Modules\Moonlaunch\MoonShine\Resources\AdminResource;
use Modules\Moonlaunch\MoonShine\Resources\RoleResource;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use Sweet1s\MoonshineRBAC\Components\MenuRBAC;
use Sweet1s\MoonshineRBAC\Resource\PermissionResource;

class Launch
{
    public function getResources(): array
    {
        return [
            AdminResource::class,
            RoleResource::class,
            PermissionResource::class,
        ];
    }

    public function getMenu(): array
    {
        return MenuRBAC::menu(
            MenuGroup::make('system', [
                MenuItem::make('admins_title', AdminResource::class)
                    ->translatable('moonlaunch::ui.resource'),

                MenuItem::make('roles', RoleResource::class)
                    ->translatable('moonlaunch::ui.resource'),

                MenuItem::make('permissions', PermissionResource::class)
                    ->translatable('moonshine-rbac::ui')
                    ->icon('s.shield-check'),
            ])
                ->translatable('moonshine::ui.resource')
                ->icon('m.cube')
        );
    }
}
