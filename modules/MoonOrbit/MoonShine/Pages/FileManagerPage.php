<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\MoonShine\Pages;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Modules\MoonOrbit\Models\Media;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Crud\JsonResponse;
use MoonShine\Laravel\MoonShineAuth;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\Enums\ToastType;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\CardsBuilder;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\File;

class FileManagerPage extends Page
{
    private const MAX_KB = 10240;

    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'webp', 'gif',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'zip',
    ];

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
        return $this->title ?: __('moon-orbit::ui.file_manager.title');
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
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        $trashed = request()->boolean('trashed');
        $media = $this->media($trashed);

        $components = [];

        if (! $trashed) {
            $components[] = FormBuilder::make()
                ->asyncMethod('uploadFiles', page: $this)
                ->submit(__('moon-orbit::ui.file_manager.upload'), ['class' => 'btn-primary'])
                ->fields([
                    Box::make(__('moon-orbit::ui.file_manager.upload_title'), [
                        File::make(__('moon-orbit::ui.file_manager.files'), 'files')
                            ->multiple()
                            ->disk(Media::DISK)
                            ->dir(Media::DIR)
                            ->allowedExtensions(self::ALLOWED_EXTENSIONS)
                            ->hint(__('moon-orbit::ui.file_manager.hint')),
                    ])->icon('s.arrow-up-tray'),
                ]);
        }

        $components[] = Box::make(
            $trashed ? __('moon-orbit::ui.file_manager.trash') : __('moon-orbit::ui.file_manager.files'),
            $media->isEmpty()
                ? [Heading::make(__($trashed ? 'moon-orbit::ui.file_manager.trash_empty' : 'moon-orbit::ui.file_manager.empty'))]
                : [$this->grid($media)],
        )->icon($trashed ? 's.trash' : 's.folder');

        $components[] = ActionButton::make(
            $trashed ? __('moon-orbit::ui.file_manager.back') : __('moon-orbit::ui.file_manager.open_trash'),
            $this->pageUrl(! $trashed),
        );

        return $components;
    }

    /**
     * @param  Collection<int, Media>  $media
     */
    private function grid(Collection $media): CardsBuilder
    {
        $trashed = request()->boolean('trashed');

        $buttons = $trashed
            ? [
                ActionButton::make(__('moon-orbit::ui.file_manager.restore'))
                    ->method('restoreMedia', params: fn (Media $item): array => ['id' => $item->id], page: $this)
                    ->success(),
                ActionButton::make(__('moon-orbit::ui.file_manager.force_delete'))
                    ->method('forceDeleteMedia', params: fn (Media $item): array => ['id' => $item->id], page: $this)
                    ->withConfirm(
                        title: __('moon-orbit::ui.file_manager.force_delete_confirm'),
                        button: __('moon-orbit::ui.file_manager.force_delete'),
                    )
                    ->error(),
            ]
            : [
                ActionButton::make(__('moon-orbit::ui.file_manager.delete'))
                    ->method('deleteMedia', params: fn (Media $item): array => ['id' => $item->id], page: $this)
                    ->withConfirm(
                        title: __('moon-orbit::ui.file_manager.delete_confirm'),
                        button: __('moon-orbit::ui.file_manager.delete'),
                    )
                    ->error(),
            ];

        return CardsBuilder::make($media, [])
            ->title(fn (Media $item): string => $item->name)
            ->subtitle(fn (Media $item): string => Number::fileSize($item->size))
            ->thumbnail(fn (Media $item): string => $item->isImage() ? $item->url() : '')
            ->url(fn (Media $item): string => $item->url())
            ->buttons($buttons);
    }

    #[AsyncMethod]
    public function uploadFiles(Request $request): JsonResponse
    {
        if (! $this->authorize()) {
            return $this->forbidden();
        }

        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['file', 'max:'.self::MAX_KB, 'mimes:'.implode(',', self::ALLOWED_EXTENSIONS)],
        ]);

        $uploader = MoonShineAuth::getGuard()->user()?->getKey();

        foreach ($request->file('files') as $file) {
            Media::query()->create([
                'name' => $file->getClientOriginalName(),
                'disk' => Media::DISK,
                'path' => $file->store(Media::DIR, Media::DISK),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploader_id' => $uploader,
            ]);
        }

        return $this->redirect(__('moon-orbit::ui.file_manager.uploaded'));
    }

    #[AsyncMethod]
    public function deleteMedia(Request $request): JsonResponse
    {
        if (! $this->authorize()) {
            return $this->forbidden();
        }

        $media = Media::query()->find($request->integer('id'));

        if ($media === null) {
            return $this->invalid();
        }

        $media->delete();

        return $this->redirect(__('moon-orbit::ui.file_manager.deleted'));
    }

    #[AsyncMethod]
    public function restoreMedia(Request $request): JsonResponse
    {
        if (! $this->authorize()) {
            return $this->forbidden();
        }

        $media = Media::withTrashed()->find($request->integer('id'));

        if ($media === null) {
            return $this->invalid();
        }

        $media->restore();

        return $this->redirect(__('moon-orbit::ui.file_manager.restored'), trashed: true);
    }

    #[AsyncMethod]
    public function forceDeleteMedia(Request $request): JsonResponse
    {
        if (! $this->authorize()) {
            return $this->forbidden();
        }

        $media = Media::withTrashed()->find($request->integer('id'));

        if ($media === null) {
            return $this->invalid();
        }

        Storage::disk($media->disk)->delete($media->path);
        $media->forceDelete();

        return $this->redirect(__('moon-orbit::ui.file_manager.force_deleted'), trashed: true);
    }

    /**
     * @return Collection<int, Media>
     */
    private function media(bool $trashed): Collection
    {
        return Media::query()
            ->when($trashed, fn ($query) => $query->onlyTrashed())
            ->latest()
            ->get();
    }

    private function pageUrl(bool $trashed): string
    {
        return route('moonshine.page', array_filter([
            'pageUri' => 'file-manager-page',
            'trashed' => $trashed ? 1 : null,
        ]));
    }

    private function forbidden(): JsonResponse
    {
        return JsonResponse::make()
            ->setStatusCode(Response::HTTP_FORBIDDEN)
            ->toast(__('moon-orbit::ui.file_manager.forbidden'), ToastType::ERROR);
    }

    private function invalid(): JsonResponse
    {
        return JsonResponse::make()
            ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->toast(__('moon-orbit::ui.file_manager.invalid'), ToastType::ERROR);
    }

    private function redirect(string $message, bool $trashed = false): JsonResponse
    {
        return JsonResponse::make()
            ->redirect($this->pageUrl($trashed))
            ->toast($message, ToastType::SUCCESS);
    }
}
