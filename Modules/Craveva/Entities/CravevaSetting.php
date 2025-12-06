<?php

namespace Modules\Craveva\Entities;

use App\Models\BaseModel;

class CravevaSetting extends BaseModel
{
    protected $table = 'craveva_settings';
    protected $guarded = ['id'];

    const MODULE_NAME = 'craveva';
}

