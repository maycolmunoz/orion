<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\Providers;

use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\MenuManager\MenuManagerContract;

class MoonOrbitServiceProvider extends ServiceProvider
{
    public function boot(CoreContract $core, MenuManagerContract $menu): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'moon-orbit');
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');
    }
}
