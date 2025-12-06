<?php

namespace Modules\Zoom\Traits;

use Illuminate\Support\Facades\Config;
use Modules\Zoom\Entities\ZoomSetting;

trait ZoomSettingsTrait
{
    public function setZoomConfigs()
    {
        $settings = ZoomSetting::first();
        $key = ($settings && $settings->api_key) ? $settings->api_key : env('ZOOM_CLIENT_ID');
        $apiSecret = ($settings && $settings->secret_key) ? $settings->secret_key : env('ZOOM_CLIENT_SECRET');
        $accountId = ($settings && $settings->account_id) ? $settings->account_id : env('ZOOM_ACCOUNT_ID');

        Config::set('zoom.client_id', $key);
        Config::set('zoom.client_secret', $apiSecret);
        Config::set('zoom.account_id', $accountId);
    }
}
