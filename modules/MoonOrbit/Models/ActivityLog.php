<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\MoonLaunch\Models\User;
use MoonShine\Laravel\MoonShineAuth;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_name
 * @property string $event
 * @property string|null $subject_type
 * @property string|null $subject_id
 * @property string|null $subject_label
 * @property array<string, mixed>|null $changes
 */
final class ActivityLog extends Model
{
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param  array<string, mixed>|null  $changes
     */
    public static function record(string $event, Model $subject, ?array $changes = null): self
    {
        $user = MoonShineAuth::getGuard()->user();

        return self::query()->create([
            'user_id' => $user?->getKey(),
            'user_name' => $user?->name,
            'event' => $event,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'subject_label' => (string) ($subject->getAttribute('name') ?? $subject->getKey()),
            'changes' => $changes,
        ]);
    }
}
