<?php

namespace Modules\Moonlaunch\Models;

use Spatie\Permission\Models\Role as ModelsRole;
use Sweet1s\MoonshineRBAC\Traits\HasMoonShineRolePermissions;

class Role extends ModelsRole
{
    use HasMoonShineRolePermissions;

    protected $with = ['permissions'];
}
