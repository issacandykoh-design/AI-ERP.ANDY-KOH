<?php


use IvanoMatteo\LaravelDeviceTracking\DeviceHijackingDetectorDefault;

return [
    // if user_model is null, will be probed: App\Model\User and then App\User
    'user_model' => \App\Models\UserAuth::class,

    // if user_model is App\Model\User, model_relation_id will be user_id
    'model_relation_id' => 'user_auth_id',

    'device_table' => 'track_devices',

    'detect_on_login' => false, // Temporarily disabled due to package bug using 'user_id' instead of 'user_auth_id'

    'geoip_provider' => null, // must implement: IvanoMatteo\LaravelDeviceTracking\GeoIpProvider

    // the device identifier cookie
    'device_cookie' => 'device_uuid',

    'cookie_http_only' => true,

    'session_key' => 'laravel-device-tracking',

    // must implement: IvanoMatteo\LaravelDeviceTracking\DeviceHijackingDetector
    'hijacking_detector' => DeviceHijackingDetectorDefault::class,
];
