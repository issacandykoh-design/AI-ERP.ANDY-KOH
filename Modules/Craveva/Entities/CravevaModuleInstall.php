<?php

namespace Modules\Craveva\Entities;

use App\Models\BaseModel;

class CravevaModuleInstall extends BaseModel
{
    protected $table = 'craveva_module_installs';
    protected $guarded = ['id'];

}
