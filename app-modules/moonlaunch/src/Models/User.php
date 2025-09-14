<?php

namespace Modules\Moonlaunch\Models;

use App\Models\User as ModelsUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sweet1s\MoonshineRBAC\Traits\MoonshineRBACHasRoles;

class User extends ModelsUser
{
    use MoonshineRBACHasRoles, SoftDeletes;

    const SUPER_ADMIN_ROLE_ID = 1;
}
