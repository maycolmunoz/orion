<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\MoonShine\Pages;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\MoonOrbit\Models\Setting;
use MoonShine\ColorManager\Palettes\CyanPalette;
use MoonShine\ColorManager\Palettes\GreenPalette;
use MoonShine\ColorManager\Palettes\HalloweenPalette;
use MoonShine\ColorManager\Palettes\NeutralPalette;
use MoonShine\ColorManager\Palettes\OrangePalette;
use MoonShine\ColorManager\Palettes\PinkPalette;
use MoonShine\ColorManager\Palettes\PurplePalette;
use MoonShine\ColorManager\Palettes\RetroPalette;
use MoonShine\ColorManager\Palettes\YellowPalette;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Crud\JsonResponse;
use MoonShine\Laravel\MoonShineAuth;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\Enums\ToastType;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

class SettingsPage extends Page
{
    private const LOGO_DIR = 'moonshine/logo';

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
        return $this->title ?: __('moon-orbit::ui.settings.title');
    }

    protected function onLoad(): void
    {
        parent::onLoad();

        abort_unless($this->authorize(), Response::HTTP_FORBIDDEN);
    }

    private function authorize(): bool
    {
        return MoonShineAuth::getGuard()->user()?->hasRole('Super Admin') === true;
    }

    /**
     * @return array<class-string, string>
     */
    public static function palettes(): array
    {
        return [
            CyanPalette::class => 'Cyan',
            GreenPalette::class => 'Green',
            HalloweenPalette::class => 'Halloween',
            NeutralPalette::class => 'Neutral',
            OrangePalette::class => 'Orange',
            PinkPalette::class => 'Pink',
            PurplePalette::class => 'Purple',
            RetroPalette::class => 'Retro',
            YellowPalette::class => 'Yellow',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function layouts(): array
    {
        return [
            'sidebar' => 'Sidebar',
            'topbar' => 'Topbar',
        ];
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [
            FormBuilder::make()
                ->asyncMethod('saveSettings', page: $this)
                ->fill(Setting::values())
                ->submit(__('moon-orbit::ui.settings.save'), ['class' => 'btn-primary'])
                ->fields([
                    Box::make(__('moon-orbit::ui.settings.branding'), [
                        Grid::make([
                            Column::make([
                                Text::make(__('moon-orbit::ui.settings.app_name'), 'app_name')
                                    ->required(),
                            ], colSpan: 7, adaptiveColSpan: 12),
                            Column::make([
                                Image::make(__('moon-orbit::ui.settings.logo'), 'logo')
                                    ->disk('public')
                                    ->dir(self::LOGO_DIR)
                                    ->allowedExtensions(['jpg', 'jpeg', 'png', 'webp'])
                                    ->hint(__('moon-orbit::ui.settings.logo_hint'))
                                    ->removable(),
                            ], colSpan: 5, adaptiveColSpan: 12),
                        ]),
                    ])->icon('s.identification'),
                    Box::make(__('moon-orbit::ui.settings.appearance'), [
                        Grid::make([
                            Column::make([
                                Select::make(__('moon-orbit::ui.settings.palette'), 'palette')
                                    ->options(self::palettes()),
                            ], colSpan: 6, adaptiveColSpan: 12),
                            Column::make([
                                Select::make(__('moon-orbit::ui.settings.layout'), 'layout')
                                    ->options(self::layouts())
                                    ->hint(__('moon-orbit::ui.settings.layout_hint')),
                            ], colSpan: 6, adaptiveColSpan: 12),
                        ]),
                    ])->icon('s.paint-brush'),
                ]),
        ];
    }

    #[AsyncMethod]
    public function saveSettings(Request $request): JsonResponse
    {
        if (! $this->authorize()) {
            return JsonResponse::make()
                ->setStatusCode(Response::HTTP_FORBIDDEN)
                ->toast(__('moon-orbit::ui.settings.forbidden'), ToastType::ERROR);
        }

        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'hidden_logo' => ['nullable', 'string', 'max:255'],
            'palette' => ['nullable', 'string', Rule::in(array_keys(self::palettes()))],
            'layout' => ['nullable', 'string', Rule::in(array_keys(self::layouts()))],
        ]);

        $previousLogo = Setting::get('logo');

        $logo = $request->hasFile('logo')
            ? $request->file('logo')->store(self::LOGO_DIR, 'public')
            : ($data['hidden_logo'] ?? null);

        Setting::put([
            'app_name' => $data['app_name'],
            'logo' => $logo,
            'palette' => $data['palette'] ?? null,
            'layout' => $data['layout'] ?? null,
        ]);

        if ($previousLogo !== null && $previousLogo !== $logo) {
            Storage::disk('public')->delete($previousLogo);
        }

        return JsonResponse::make()
            ->redirect(route('moonshine.page', 'settings-page'))
            ->toast(__('moon-orbit::ui.settings.saved'), ToastType::SUCCESS);
    }
}
