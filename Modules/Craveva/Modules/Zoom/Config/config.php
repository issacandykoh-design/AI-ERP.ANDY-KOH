<?php

$addOnOf = 'craveva-saas-new';

return [
    'name' => 'Zoom',
    'verification_required' => true,
    'craveva_item_id' => 29292666,
    'parent_craveva_id' => 23263417,
    'parent_min_version' => '5.2.5',
    'script_name' => $addOnOf . '-zoom-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Zoom\Entities\ZoomGlobalSetting::class
];
