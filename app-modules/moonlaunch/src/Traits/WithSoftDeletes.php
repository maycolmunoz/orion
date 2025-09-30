<?php

namespace Modules\Moonlaunch\Traits;

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
    protected function indexButtons(): ListOf
    {
        return parent::indexButtons()
            ->prepend(
                ActionButton::make('')->icon('s.arrow-uturn-right')
                    ->customAttributes(['title' => __('moonlaunch::ui.soft_deletes.force_delete')])
                    ->method('forceDelete', events: [$this->getListEventName()])
                    ->canSee(fn ($model) => $model->trashed())
                    ->withConfirm(__('moonlaunch::ui.soft_deletes.force_delete')),

                ActionButton::make('')->icon('s.arrow-uturn-left')
                    ->customAttributes(['title' => __('moonlaunch::ui.soft_deletes.restore')])
                    ->method('restore', events: [$this->getListEventName()])
                    ->canSee(fn ($model) => $model->trashed())
                    ->withConfirm(__('moonlaunch::ui.soft_deletes.restore')),
            );
    }

    protected function queryTags(): array
    {
        return [
            QueryTag::make(
                __('moonlaunch::ui.soft_deletes.trashed'),
                static fn (Builder $q) => $q->onlyTrashed()
            ),
        ];
    }

    protected function modifyItemQueryBuilder(Builder $builder): Builder
    {
        return $builder->withTrashed();
    }

    public function restore(MoonShineRequest $request): MoonShineJsonResponse
    {
        $item = $request->getResource()->getItem();
        $item->restore();

        return MoonShineJsonResponse::make()
            ->toast(
                __('moonlaunch::ui.soft_deletes.item_restored'),
                ToastType::SUCCESS
            );
    }

    public function forceDelete(MoonShineRequest $request): MoonShineJsonResponse
    {
        $item = $request->getResource()->getItem();
        $item->forceDelete();

        return MoonShineJsonResponse::make()
            ->toast(
                __('moonlaunch::ui.soft_deletes.item_deleted'),
                ToastType::SUCCESS
            );
    }

    protected function modifyDeleteButton(ActionButtonContract $button): ActionButtonContract
    {
        return $button->canSee(fn ($model) => ! $model->trashed());
    }

    protected function modifyMassDeleteButton(ActionButtonContract $button): ActionButtonContract
    {
        return $button->canSee(fn () => request()->input('query-tag') !== 'deleted');
    }
}
