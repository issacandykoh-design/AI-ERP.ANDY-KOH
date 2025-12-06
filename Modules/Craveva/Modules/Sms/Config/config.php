<?php

$addOnOf = 'craveva-saas-new';

return [
    'name' => 'Sms',
    'verification_required' => true,
    'craveva_item_id' => 27915117,
    'parent_craveva_id' => 23263417,
    'parent_min_version' => '5.2.3',
    'script_name' => $addOnOf.'-sms-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Sms\Entities\SmsSetting::class,
];
