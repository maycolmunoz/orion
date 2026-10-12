<?php

declare(strict_types=1);

namespace Modules\MoonLaunch\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\MoonLaunch\Console\Commands\LaunchInstall;
use Modules\MoonLaunch\Console\Commands\LaunchPermissions;
use Modules\MoonLaunch\MoonShine\Pages\Dashboard;
use Modules\MoonLaunch\MoonShine\Resources\Admin\AdminResource;
use Modules\MoonLaunch\MoonShine\Resources\Role\RoleResource;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\MenuManager\MenuManagerContract;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use Sweet1s\MoonshineRBAC\Components\MenuRBAC;
use Sweet1s\MoonshineRBAC\Resource\PermissionResource;

class MoonLaunchServiceProvider extends ServiceProvider
{
    public function boot(CoreContract $core, MenuManagerContract $menu): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'moon-launch');
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');

        $this->registerCommands();
        $this->registerResources($core);
        $this->registerMenu($menu);
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                LaunchInstall::class,
                LaunchPermissions::class,
            ]);
        }
    }

    private function registerResources(CoreContract $core): void
    {
        $core->resources([
            AdminResource::class,
            RoleResource::class,
            PermissionResource::class,
        ]);
    }

    private function registerMenu(MenuManagerContract $menu): void
    {
        $menu->add(MenuRBAC::menu(
            MenuItem::make(Dashboard::class)->icon('s.home'),

            MenuGroup::make('system', [
                MenuItem::make(AdminResource::class, 'admins_title')
                    ->translatable('moon-launch::ui.resource'),
                MenuItem::make(RoleResource::class, 'roles')
                    ->translatable('moon-launch::ui.resource'),
                MenuItem::make(PermissionResource::class, 'permissions')
                    ->icon('s.shield-check')
                    ->translatable('moonshine-rbac::ui'),
            ])
                ->translatable('moonshine::ui.resource')
                ->icon('m.cube')
        ));
    }
}
