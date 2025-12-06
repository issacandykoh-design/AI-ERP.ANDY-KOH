<?php

$addOnOf = 'craveva-saas-new';

return [
    'name' => 'QRCode',
    'verification_required' => true,
    'craveva_item_id' => 50328651,
    'parent_craveva_id' => 23263417,
    'parent_min_version' => '5.3.6',
    'script_name' => $addOnOf.'-qrcode-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\QRCode\Entities\QRCodeSetting::class,
];
