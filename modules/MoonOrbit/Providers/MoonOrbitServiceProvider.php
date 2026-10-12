<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\Providers;

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\Models\User;
use Modules\MoonOrbit\Console\Commands\MediaSync;
use Modules\MoonOrbit\Console\Commands\PruneActivityLog;
use Modules\MoonOrbit\Models\Media;
use Modules\MoonOrbit\Models\Setting;
use Modules\MoonOrbit\MoonShine\Pages\FileManagerPage;
use Modules\MoonOrbit\MoonShine\Pages\SettingsPage;
use Modules\MoonOrbit\MoonShine\Resources\ActivityLog\ActivityLogResource;
use Modules\MoonOrbit\Observers\ActivityObserver;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\MenuManager\MenuManagerContract;
use MoonShine\Laravel\MoonShineAuth;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use Sweet1s\MoonshineRBAC\Components\MenuRBAC;

class MoonOrbitServiceProvider extends ServiceProvider
{
    public function boot(
        CoreContract $core,
        MenuManagerContract $menu,
        ConfiguratorContract $config,
    ): void {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'moon-orbit');
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerObservers();
        $this->registerCommands();
        $this->registerPages($core);
        $this->registerResources($core);
        $this->registerMenu($menu);
        $this->registerConfig($config);
    }

    private function registerObservers(): void
    {
        User::observe(ActivityObserver::class);
        Role::observe(ActivityObserver::class);
        Setting::observe(ActivityObserver::class);
        Media::observe(ActivityObserver::class);
    }

    private function registerCommands(): void
    {
        $this->commands([
            MediaSync::class,
            PruneActivityLog::class,
        ]);

        Schedule::command('orbit:activity:prune')->daily();
    }

    private function registerPages(CoreContract $core): void
    {
        $core->pages([
            SettingsPage::class,
            FileManagerPage::class,
        ]);
    }

    private function registerResources(CoreContract $core): void
    {
        $core->resources([
            ActivityLogResource::class,
        ]);
    }

    private function registerMenu(MenuManagerContract $menu): void
    {
        $menu->add(MenuRBAC::menu(
            MenuGroup::make('orbit', [
                MenuItem::make(SettingsPage::class, 'settings.title')
                    ->icon('s.cog-6-tooth')
                    ->translatable('moon-orbit::ui')
                    ->canSee(self::canAccess(...)),
                MenuItem::make(ActivityLogResource::class, 'activity_log.title')
                    ->icon('s.clock')
                    ->translatable('moon-orbit::ui'),
                MenuItem::make(FileManagerPage::class, 'file_manager.title')
                    ->icon('s.folder')
                    ->translatable('moon-orbit::ui')
                    ->canSee(self::canAccess(...)),
            ])
                ->icon('m.moon')
                ->translatable('moon-orbit::ui'),
        ));
    }

    private function registerConfig(ConfiguratorContract $config): void
    {
        $config
            ->set('title', self::appName(...))
            ->set('logo', self::logo(...))
            ->set('logo_small', self::logo(...))
            ->set('palette', self::palette(...))
            ->set('layout_mode', self::layoutMode(...));
    }

    private static function canAccess(): bool
    {
        return MoonShineAuth::getGuard()->user()?->isSuperAdmin() === true;
    }

    private static function appName(): string
    {
        return Setting::get('app_name') ?? config('moonshine.title');
    }

    private static function logo(): ?string
    {
        $logo = Setting::get('logo');

        return $logo === null ? null : Storage::disk('public')->url($logo);
    }

    private static function palette(): string
    {
        $palette = Setting::get('palette');

        return $palette !== null && array_key_exists($palette, SettingsPage::palettes())
            ? $palette
            : config('moonshine.palette');
    }

    private static function layoutMode(): string
    {
        $layout = Setting::get('layout');

        return $layout !== null && array_key_exists($layout, SettingsPage::layouts())
            ? $layout
            : 'sidebar';
    }
}
