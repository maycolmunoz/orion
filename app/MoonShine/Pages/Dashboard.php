<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use Composer\InstalledVersions;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;

class Dashboard extends Page
{
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Dashboard';
    }

    private function version(string $pkg): string
    {
        return InstalledVersions::getPrettyVersion($pkg);
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [
            Grid::make([
                ValueMetric::make('PHP')
                    ->value(fn () => PHP_VERSION)
                    ->columnSpan(4)
                    ->icon('s.cube'),

                ValueMetric::make('Laravel')
                    ->value(fn () => $this->version('laravel/framework'))
                    ->columnSpan(4)
                    ->icon('s.cube'),

                ValueMetric::make('Modular')
                    ->value(fn () => $this->version('internachi/modular'))
                    ->columnSpan(4)
                    ->icon('s.cube'),

                ValueMetric::make('MoonShine')
                    ->value(fn () => $this->version('moonshine/moonshine'))
                    ->columnSpan(4)
                    ->icon('s.cube'),

                ValueMetric::make('Roles & Permissions')
                    ->value(fn () => $this->version('sweet1s/moonshine-roles-permissions'))
                    ->columnSpan(4)
                    ->icon('s.cube'),
            ]),
        ];
    }
}
