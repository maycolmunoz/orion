<?php

namespace Modules\MoonLaunch\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Contracts\Core\DependencyInjection\CrudRequestContract;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Crud\JsonResponse;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\Enums\ToastType;
use MoonShine\UI\Components\ActionButton;

trait WithSoftDeletes
{
    /**
     * trashActions
     */
    protected function trashActions(): array
    {
        return [
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
        ];
    }

    /**
     * trashedTag
     */
    protected function trashedTag(): array
    {
        return [
            QueryTag::make(
                __('moon-launch::ui.soft_deletes.trashed'),
                static fn (Builder $q) => $q->onlyTrashed()
            ),
        ];
    }

    #[AsyncMethod]
    /**
     * restore
     *
     * @param  mixed  $request
     */
    public function restore(CrudRequestContract $request): JsonResponse
    {
        $item = $request->getResource()->getItem();
        $item->restore();

        return JsonResponse::make()
            ->toast(
                __('moon-launch::ui.soft_deletes.item_restored'),
                ToastType::SUCCESS
            );
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

    #[AsyncMethod]
    /**
     * forceDelete
     *
     * @param  mixed  $request
     */
    public function forceDelete(CrudRequestContract $request): JsonResponse
    {
        $item = $request->getResource()->getItem();
        $item->forceDelete();

        return JsonResponse::make()
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
     * modifyEditButton
     *
     * @param  mixed  $button
     */
    protected function modifyEditButton(ActionButtonContract $button): ActionButtonContract
    {
        return $button->canSee(fn ($model) => ! $model->trashed());
    }

    /**
     * modifyDetailButton
     *
     * @param  mixed  $button
     */
    protected function modifyDetailButton(ActionButtonContract $button): ActionButtonContract
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
