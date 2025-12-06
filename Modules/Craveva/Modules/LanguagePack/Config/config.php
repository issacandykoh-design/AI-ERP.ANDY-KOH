<?php

$addOnOf = 'craveva-saas-new';

return [
    'name' => 'LanguagePack',
    'verification_required' => true,
    'craveva_item_id' => 48773832,
    'parent_craveva_id' => 23263417,
    'parent_min_version' => '5.3.3',
    'script_name' => $addOnOf.'-languagepack-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\LanguagePack\Entities\LanguagePackSetting::class,
];
