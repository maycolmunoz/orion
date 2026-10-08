<?php

declare(strict_types=1);

namespace Modules\MoonLaunch\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use MoonShine\Contracts\Core\CrudResourceContract;
use MoonShine\Contracts\Core\DependencyInjection\CrudRequestContract;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Crud\JsonResponse;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\Enums\Ability;
use MoonShine\Support\Enums\ToastType;
use MoonShine\UI\Components\ActionButton;

trait WithSoftDeletes
{
    /**
     * trashActions
     */
    protected function trashActions(): array
    {
        $forceDelete = $this->canAction(Ability::FORCE_DELETE);
        $restore = $this->canAction(Ability::RESTORE);

        return [
            ActionButton::make('')->icon('s.arrow-uturn-right')
                ->customAttributes(['title' => __('moon-launch::ui.soft_deletes.force_delete')])
                ->method('forceDelete', events: [$this->getListEventName()])
                ->canSee(fn ($model) => $model->trashed() && $forceDelete)
                ->withConfirm(__('moon-launch::ui.soft_deletes.force_delete')),
            ActionButton::make('')->icon('s.arrow-uturn-left')
                ->customAttributes(['title' => __('moon-launch::ui.soft_deletes.restore')])
                ->method('restore', events: [$this->getListEventName()])
                ->canSee(fn ($model) => $model->trashed() && $restore)
                ->withConfirm(__('moon-launch::ui.soft_deletes.restore')),
        ];
    }

    private function canAction(Ability $ability): bool
    {
        $resource = $this->getResource();

        return $resource instanceof CrudResourceContract && $resource->can($ability);
    }

    private function forbiddenAction(): JsonResponse
    {
        return JsonResponse::make()
            ->setStatusCode(Response::HTTP_FORBIDDEN)
            ->toast(__('moon-launch::ui.soft_deletes.forbidden'), ToastType::ERROR);
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
            )->alias('deleted'),
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
        $resource = $request->getResource();

        if ($resource === null || ! $resource->can(Ability::RESTORE)) {
            return $this->forbiddenAction();
        }

        $item = $resource->getItem();
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
        $resource = $request->getResource();

        if ($resource === null || ! $resource->can(Ability::FORCE_DELETE)) {
            return $this->forbiddenAction();
        }

        $item = $resource->getItem();
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
