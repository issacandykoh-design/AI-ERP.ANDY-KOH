<?php

$addOnOf = 'craveva-saas-new';

return [
    'name' => 'Craveva',
    'verification_required' => true,
    'craveva_item_id' => 48913734,
    'parent_craveva_id' => 23263417,
    'parent_min_version' => '5.3.4',
    'script_name' => $addOnOf.'-craveva-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Craveva\Entities\CravevaSetting::class,
];
