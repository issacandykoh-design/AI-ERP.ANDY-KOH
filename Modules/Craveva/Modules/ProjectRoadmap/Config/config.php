<?php

$addOnOf = 'craveva-saas-new';

return [
    'name' => 'ProjectRoadmap',
    'verification_required' => true,
    'craveva_item_id' => 49417254,
    'parent_craveva_id' => 23263417,
    'parent_min_version' => '5.3.54',
    'script_name' => $addOnOf . '-projectroadmap-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\ProjectRoadmap\Entities\ProjectRoadmapSetting::class,
];
