<?php

$addOnOf = 'craveva-saas-new';

return [
    'name' => 'Affiliate',
    'verification_required' => true,
    'craveva_item_id' => 54299368,
    'parent_craveva_id' => 23263417,
    'parent_min_version' => '5.3.9',
    'script_name' => $addOnOf . '-affiliate-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Affiliate\Entities\AffiliateGlobalSetting::class,
];
