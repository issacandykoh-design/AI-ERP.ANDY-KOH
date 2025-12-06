<?php

$addOnOf = 'craveva-saas-new';

return [
    'name' => 'Recruit',
    'verification_required' => true,
    'craveva_item_id' => 43314875,
    'parent_craveva_id' => 23263417,
    'parent_min_version' => '5.2.5',
    'script_name' => $addOnOf . '-recruit-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Recruit\Entities\RecruitGlobalSetting::class,
];
