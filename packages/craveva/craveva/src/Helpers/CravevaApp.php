<?php

namespace Craveva\Craveva\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CravevaApp
{

    const craveva_URL = 'https://craveva.net/checkout/from_item/';
    const CACHE_MINUTE = 30;

    public static function isLocalHost(): bool
    {
        $domain = request()->getHost();

        $localHosts = [
            'localhost',
            '127.0.0.1',
            '::1',
        ];

        if (in_array($domain, $localHosts)) {
            return true;
        }


        $allowedDomains = [
            '.test',
            '.local',
            'ngrok',
        ];

        //Ignore of IP
        if (ip2long($domain)) {
            return true;
        }

        foreach ($allowedDomains as $allowedDomain) {
            if (str_contains($domain, $allowedDomain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws GuzzleException
     */
    public static function getRemoteData($url, $method = 'GET')
    {
        if (cache()->has($url)) {
            return cache($url);
        }

        try {
            $client = new Client();
            $res = $client->request($method, $url, ['verify' => false]);
            $body = $res->getBody();

            $content = json_decode($body, true);
            cache([$url => $content], now()->addMinutes(self::CACHE_MINUTE));

            return $content;
        } catch (\Exception $e) {
            return null;
        }

    }

    public static function buyExtendedUrl($cravevaId): string
    {
        return self::craveva_URL . $cravevaId . '?license=extended';
    }

    public static function renewSupportUrl($cravevaId): string
    {
        return self::craveva_URL . $cravevaId . '?support=renew_6month';
    }

    public static function extendSupportUrl($cravevaId): string
    {
        return self::craveva_URL . $cravevaId . '?support=extend_6month';
    }

}
