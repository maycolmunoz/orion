<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Modules\MoonLaunch\Models\User;

/**
 * @property int $id
 * @property string $name
 * @property string $disk
 * @property string $path
 * @property string|null $mime_type
 * @property int $size
 * @property string|null $alt
 * @property int|null $uploader_id
 */
final class Media extends Model
{
    use SoftDeletes;

    public const DISK = 'public';

    public const DIR = 'files';

    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    protected $table = 'media';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id')->withTrashed();
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function isImage(): bool
    {
        return in_array(strtolower(pathinfo($this->path, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true);
    }
}
