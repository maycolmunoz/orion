<?php

namespace Modules\Moonlaunch\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Moonlaunch\Services\Launch;
use Modules\Moonlaunch\Services\ThemeApplier;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;

class MoonlaunchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Launch::class);
    }

    public function boot(
        CoreContract $core,
        ColorManagerContract $colorManager,
        Launch $launch
    ): void {

        $core->resources($launch->getResources());

        (new ThemeApplier($colorManager))->theme1();
        // (new ThemeApplier($colorManager))->theme2();
        // (new ThemeApplier($colorManager))->theme3();
        // (new ThemeApplier($colorManager))->theme4();
    }
}
