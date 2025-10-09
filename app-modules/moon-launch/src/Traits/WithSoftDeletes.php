<?php

namespace Modules\MoonLaunch\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Http\Responses\MoonShineJsonResponse;
use MoonShine\Laravel\MoonShineRequest;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;

trait WithSoftDeletes
{
    /**
     * indexButtons
     */
    protected function indexButtons(): ListOf
    {
        return parent::indexButtons()
            ->prepend(
                ActionButton::make('')->icon('s.arrow-uturn-right')
                    ->customAttributes(['title' => __('moon-launch::ui.soft_deletes.force_delete')])
                    ->method('forceDelete', events: [$this->getListEventName()])
                    ->canSee(fn ($model) => $model->trashed())
                    ->withConfirm(__('moon-launch::ui.soft_deletes.force_delete')),

                ActionButton::make('')->icon('s.arrow-uturn-left')
                    ->customAttributes(['title' => __('moon-launch::ui.soft_deletes.restore')])
                    ->method('restore', events: [$this->getListEventName()])
                    ->canSee(fn ($model) => $model->trashed())
                    ->withConfirm(__('moon-launch::ui.soft_deletes.restore')),
            );
    }

    /**
     * queryTags
     */
    protected function queryTags(): array
    {
        return [
            QueryTag::make(
                __('moon-launch::ui.soft_deletes.trashed'),
                static fn (Builder $q) => $q->onlyTrashed()
            ),
        ];
    }

    /**
     * modifyItemQueryBuilder
     *
     * @param  mixed  $builder
     */
    protected function modifyItemQueryBuilder(Builder $builder): Builder
    {
        return $builder->withTrashed();
    }

    /**
     * restore
     *
     * @param  mixed  $request
     */
    public function restore(MoonShineRequest $request): MoonShineJsonResponse
    {
        $item = $request->getResource()->getItem();
        $item->restore();

        return MoonShineJsonResponse::make()
            ->toast(
                __('moon-launch::ui.soft_deletes.item_restored'),
                ToastType::SUCCESS
            );
    }

    /**
     * forceDelete
     *
     * @param  mixed  $request
     */
    public function forceDelete(MoonShineRequest $request): MoonShineJsonResponse
    {
        $item = $request->getResource()->getItem();
        $item->forceDelete();

        return MoonShineJsonResponse::make()
            ->toast(
                __('moon-launch::ui.soft_deletes.item_deleted'),
                ToastType::SUCCESS
            );
    }

    /**
     * modifyDeleteButton
     *
     * @param  mixed  $button
     */
    protected function modifyDeleteButton(ActionButtonContract $button): ActionButtonContract
    {
        return $button->canSee(fn ($model) => ! $model->trashed());
    }

    /**
     * modifyMassDeleteButton
     *
     * @param  mixed  $button
     */
    protected function modifyMassDeleteButton(ActionButtonContract $button): ActionButtonContract
    {
        return $button->canSee(fn () => request()->input('query-tag') !== 'deleted');
    }
}
