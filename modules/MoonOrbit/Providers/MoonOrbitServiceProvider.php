<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Modules\MoonLaunch\Models\Role;
use Modules\MoonLaunch\Models\User;
use Modules\MoonOrbit\Models\Setting;
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

        $core->pages([
            SettingsPage::class,
        ]);

        User::observe(ActivityObserver::class);
        Role::observe(ActivityObserver::class);
        Setting::observe(ActivityObserver::class);

        $core->resources([
            ActivityLogResource::class,
        ]);

        $menu->add(MenuRBAC::menu(
            MenuGroup::make('orbit', [
                MenuItem::make(SettingsPage::class, 'settings.title')
                    ->icon('s.cog-6-tooth')
                    ->translatable('moon-orbit::ui')
                    ->canSee(self::canAccess(...)),
                MenuItem::make(ActivityLogResource::class, 'activity_log.title')
                    ->icon('s.clock')
                    ->translatable('moon-orbit::ui'),
            ])
                ->icon('m.moon')
                ->translatable('moon-orbit::ui'),
        ));

        $config
            ->set('title', self::appName(...))
            ->set('logo', self::logo(...))
            ->set('logo_small', self::logo(...))
            ->set('palette', self::palette(...))
            ->set('layout_mode', self::layoutMode(...));
    }

    private static function canAccess(): bool
    {
        return MoonShineAuth::getGuard()->user()?->hasRole('Super Admin') === true;
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
