<?php

namespace Modules\MoonLaunch\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\MoonLaunch\Services\Launch;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;

class MoonLaunchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Launch::class);
    }

    public function boot(CoreContract $core, Launch $launch): void
    {
        $core->resources($launch->getResources());
    }
}
