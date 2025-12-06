<?php

namespace Modules\Recruit\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecruitEmailNotificationSetting extends BaseModel
{
    use HasFactory, HasCompany;

    protected $fillable = ['setting_name', 'send_email', 'slug', 'company_id'];
}
