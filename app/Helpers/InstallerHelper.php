<?php

if (!function_exists('isActive')) {
    /**
     * Check if the given route name is active.
     *
     * @param string $routeName
     * @return string
     */
    function isActive($routeName)
    {
        return request()->routeIs($routeName) ? 'step__item--active' : '';
    }
}

if (!function_exists('canInstall')) {
    /**
     * Check if the application can be installed.
     *
     * @return bool
     */
    function canInstall()
    {
        return !file_exists(storage_path('installed'));
    }
}
