<?php

declare(strict_types=1);

namespace Modules\MoonLaunch\MoonShine\Resources\Admin\Pages;

use Illuminate\Validation\Rule;
use Modules\MoonLaunch\MoonShine\Resources\Admin\AdminResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Collapse;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\PasswordRepeat;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends FormPage<AdminResource>
 */
class AdminFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                Tabs::make([
                    Tab::make(__('moon-launch::ui.resource.main_information'), [
                        ID::make()->sortable(),

                        Flex::make([
                            Text::make('name')->translatable('moon-launch::ui.resource')
                                ->required(),
                            Email::make('email')->translatable('moon-launch::ui.resource')
                                ->required(),
                        ]),

                        Flex::make([
                            Password::make('password')->translatable('moon-launch::ui.resource')
                                ->customAttributes(['autocomplete' => 'new-password'])
                                ->required()
                                ->eye(),
                            PasswordRepeat::make('password_repeat')
                                ->translatable('moon-launch::ui.resource')
                                ->customAttributes(['autocomplete' => 'confirm-password'])
                                ->required()
                                ->eye(),
                        ])->canSee(fn () => $this->resource->isCreateFormPage()),

                        // Image::make('avatar')->translatable('moon-launch::ui.resource')
                        //     ->disk(moonshineConfig()->getDisk())
                        //     ->dir('moonshine_users')
                        //     ->allowedExtensions(['jpg', 'png', 'jpeg', 'gif', 'webp']),
                    ])->icon('user-circle'),

                    Tab::make(__('moon-launch::ui.resource.password'), [
                        Collapse::make(__('moon-launch::ui.resource.change_password'), [
                            Password::make('password')->translatable('moon-launch::ui.resource')
                                ->customAttributes(['autocomplete' => 'new-password'])
                                ->eye(),
                            PasswordRepeat::make('password_repeat')->translatable('moon-launch::ui.resource')
                                ->customAttributes(['autocomplete' => 'confirm-password'])
                                ->eye(),
                        ])->icon('lock-closed'),
                    ])->icon('lock-closed')->canSee(fn () => $this->resource->isUpdateFormPage()),
                ]),
            ]),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    protected function formButtons(): ListOf
    {
        return parent::formButtons();
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'name' => 'required',
            'email' => [
                'sometimes',
                'bail',
                'required',
                'email',
                Rule::unique('users')->ignoreModel($item),
            ],
            'password' => $item->exists
                ? 'sometimes|nullable|min:6|required_with:password_repeat|same:password_repeat'
                : 'required|min:6|required_with:password_repeat|same:password_repeat',
        ];
    }

    /**
     * @param  FormBuilder  $component
     * @return FormBuilder
     */
    protected function modifyFormComponent(FormBuilderContract $component): FormBuilderContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer(),
        ];
    }
}
