<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->booting(static function () use ($app): void {
            $app->singleton(ConfiguratorContract::class, MoonShineConfigurator::class);
        });

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
