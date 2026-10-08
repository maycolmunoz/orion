<?php

declare(strict_types=1);

namespace Modules\MoonLaunch\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\MoonLaunch\Models\User;

class LaunchInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'launch:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure moonshine launch package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Generating application key...');

        if (! Str::is('base64:*', config('app.key'))) {
            $this->call('key:generate');
        } else {
            $this->info('✅ Application key already generated, skipping.');
        }

        $this->info('📦 Running migrations...');
        $this->call('migrate');

        $this->info('🔐 Generating permissions...');
        $this->call('launch:permissions');

        $this->info('👤 Creating Super Admin user...');

        if (! config('moonshine.auth.model')::role(User::SUPER_ADMIN_ROLE_ID)->exists()) {
            $this->call('moonshine-rbac:user');
        } else {
            $this->info('✅ Super Admin user already exists, skipping.');
        }

        $this->info('✅ MoonLaunch installed successfully.');

        return self::SUCCESS;
    }
}
