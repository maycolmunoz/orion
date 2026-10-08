<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\Console\Commands;

use Illuminate\Console\Command;
use Modules\MoonOrbit\Models\ActivityLog;

class PruneActivityLog extends Command
{
    protected $signature = 'orbit:activity:prune {--days=90 : Delete activity logs older than N days}';

    protected $description = 'Delete activity log records older than the given number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $deleted = ActivityLog::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Deleted {$deleted} activity log(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
