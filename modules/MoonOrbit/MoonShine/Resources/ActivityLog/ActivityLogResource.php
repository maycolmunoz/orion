<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\MoonShine\Resources\ActivityLog;

use Modules\MoonLaunch\Traits\WithProperties;
use Modules\MoonOrbit\Models\ActivityLog;
use Modules\MoonOrbit\MoonShine\Resources\ActivityLog\Pages\ActivityLogDetailPage;
use Modules\MoonOrbit\MoonShine\Resources\ActivityLog\Pages\ActivityLogIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Crud\JsonResponse;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Ability;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Support\ListOf;
use Sweet1s\MoonshineRBAC\Traits\WithRolePermissions;
use Symfony\Component\HttpFoundation\Response;

#[Icon('s.clock')]
/**
 * @extends ModelResource<ActivityLog, ActivityLogIndexPage, null, ActivityLogDetailPage>
 */
class ActivityLogResource extends ModelResource
{
    use WithProperties;
    use WithRolePermissions;

    protected string $model = ActivityLog::class;

    protected bool $detailInModal = true;

    public function __construct()
    {
        $this->title(__('moon-orbit::ui.activity_log.title'))
            ->with(['user', 'subject'])
            ->column('created_at')
            ->itemsPerPage(20);
    }

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            ActivityLogIndexPage::class,
            ActivityLogDetailPage::class,
        ];
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::CREATE, Action::UPDATE);
    }

    public static function modelName(?string $type): ?string
    {
        if (blank($type)) {
            return null;
        }

        $name = class_basename($type);
        $key = "moon-orbit::ui.activity_log.models.$name";

        return trans()->has($key) ? __($key) : $name;
    }

    public static function subjectPreview(?ActivityLog $log): string
    {
        if ($log === null) {
            return '—';
        }

        return collect([self::modelName($log->subject_type), e((string) $log->subject_label)])
            ->filter()
            ->implode(' · ') ?: '—';
    }

    #[AsyncMethod]
    public function clearAll(): JsonResponse
    {
        if (! $this->can(Ability::MASS_DELETE)) {
            return JsonResponse::make()
                ->setStatusCode(Response::HTTP_FORBIDDEN)
                ->toast(__('moon-orbit::ui.activity_log.forbidden'), ToastType::ERROR);
        }

        ActivityLog::query()->delete();

        return JsonResponse::make()
            ->redirect($this->getIndexPageUrl())
            ->toast(__('moon-orbit::ui.activity_log.cleared'), ToastType::SUCCESS);
    }
}
