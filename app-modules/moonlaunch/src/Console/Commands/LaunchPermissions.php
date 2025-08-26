<?php

namespace Modules\Moonlaunch\Console\Commands;

use Illuminate\Console\Command;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;

class LaunchPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'launch:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates the permissions for the resources registered in moonshine';

    /**
     * Execute the console command.
     */
    public function handle(CoreContract $moonshine)
    {
        foreach ($moonshine->getResources() as $item) {
            $this->call('moonshine-rbac:permissions', [
                'resourceName' => class_basename($item),
            ]);
        }

        // $this->call('moonshine-rbac:permissions', [
        //     'resourceName' => 'ExampleResource'
        // ]);

        $this->call('moonshine-rbac:role', [
            'name' => 'Super Admin',
            '--all-permissions' => true,
        ]);
    }
}
