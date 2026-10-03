<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Modules\MoonOrbit\Models\ActivityLog;

final class ActivityObserver
{
    public function created(Model $model): void
    {
        ActivityLog::record('created', $model, [
            'attributes' => self::attributes($model->getAttributes()),
        ]);
    }

    public function updated(Model $model): void
    {
        $attributes = self::attributes($model->getChanges());

        if ($attributes === []) {
            return;
        }

        ActivityLog::record('updated', $model, [
            'attributes' => $attributes,
            'old' => Arr::only($model->getOriginal(), array_keys($attributes)),
        ]);
    }

    public function deleted(Model $model): void
    {
        if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
            return;
        }

        ActivityLog::record('deleted', $model, [
            'old' => self::attributes($model->getOriginal()),
        ]);
    }

    public function restored(Model $model): void
    {
        ActivityLog::record('restored', $model, [
            'attributes' => self::attributes($model->getAttributes()),
        ]);
    }

    public function forceDeleted(Model $model): void
    {
        ActivityLog::record('forceDeleted', $model, [
            'old' => self::attributes($model->getOriginal()),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private static function attributes(array $attributes): array
    {
        return Arr::except($attributes, ['password', 'remember_token', 'updated_at']);
    }
}
