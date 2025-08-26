<?php

namespace Modules\Moonlaunch\Services;

use MoonShine\Contracts\ColorManager\ColorManagerContract;

class ThemeApplier
{
    /**
     * Create a new class instance.
     */
    public function __construct(private ColorManagerContract $colorManager) {}

    public function theme1(): void
    {
        $this->applyTheme([
            'background' => '#17202a',
            'content' => '#1c2833',
            'tableRow' => '#212f3c',
            'primary' => '#ca6f1e',
            'secondary' => '#f5b041',
            'stateColor' => '#34495e',
        ]);
    }

    public function theme2(): void
    {
        $this->applyTheme([
            'background' => '#121212',
            'content' => '#1a1a1a',
            'tableRow' => '#333333',
            'primary' => '#2980b9',
            'secondary' => '#16a085',
            'stateColor' => '#5d6d7e',
        ]);
    }

    public function theme3(): void
    {
        $this->applyTheme([
            'background' => '#1e2420',
            'content' => '#262c28',
            'tableRow' => '#323834',
            'primary' => '#10b981',
            'secondary' => '#6ee7b7',
            'stateColor' => '#4b5563',
        ]);
    }

    public function theme4(): void
    {
        $this->applyTheme([
            'background' => '#1c1b29',
            'content' => '#252438',
            'tableRow' => '#33324d',
            'primary' => '#8e44ad',
            'secondary' => '#bb8fce',
            'stateColor' => '#5d6d7e',
        ]);
    }

    private function applyTheme(array $colors): void
    {
        $this->colorManager
            ->background($colors['background'])
            ->content($colors['content'])
            ->tableRow($colors['tableRow'])
            ->primary($colors['primary'])
            ->secondary($colors['secondary'])
            ->buttons($colors['stateColor'])
            ->dividers($colors['stateColor'])
            ->borders($colors['stateColor']);

        $this->colorManager
            ->successBg('#198754')
            ->successText('#FFFFFF')
            ->warningBg('#FFC107')
            ->warningText('#FFFFFF')
            ->errorBg('#DC3545')
            ->errorText('#FFFFFF')
            ->infoBg('#0D6EFD')
            ->infoText('#FFFFFF')

            ->successBg('#198754', dark: true)
            ->successText('#FFFFFF', dark: true)
            ->warningBg('#FFC107', dark: true)
            ->warningText('#FFFFFF', dark: true)
            ->errorBg('#DC3545', dark: true)
            ->errorText('#FFFFFF', dark: true)
            ->infoBg('#0D6EFD', dark: true)
            ->infoText('#FFFFFF', dark: true);
    }
}
