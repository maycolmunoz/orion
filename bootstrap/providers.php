<?php

use App\Providers\AppServiceProvider;
use App\Providers\MoonShineServiceProvider;
use Modules\MoonLaunch\Providers\MoonLaunchServiceProvider;
use Modules\MoonOrbit\Providers\MoonOrbitServiceProvider;

return [
    AppServiceProvider::class,
    MoonShineServiceProvider::class,
    MoonLaunchServiceProvider::class,
    // MoonOrbitServiceProvider::class,
];
