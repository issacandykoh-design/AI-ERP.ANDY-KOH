<?php
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

if(!file_exists(__DIR__ . '/../.env')){
    $GLOBALS["error_type"] = "env-missing";
    include('error_install.php');
    exit(1);
}

if (version_compare(PHP_VERSION, '8.2.0') < 0){
    $GLOBALS["error_type"] = "php-version";
    include('error_install.php');
    exit(1);
}

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is maintenance / demo mode via the "down" command we
| will require this file so that any prerendered template can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists(__DIR__.'/../storage/framework/maintenance.php')) {
    require __DIR__.'/../storage/framework/maintenance.php';
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

if (!class_exists('ZipArchive')) {
    class ZipArchive { const CM_DEFAULT = 0; }
}

try {
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Kernel::class);
    $response = tap($kernel->handle(
        $request = Request::capture()
    ))->send();
    $kernel->terminate($request, $response);
} catch (\Throwable $e) {
    $candidates = [
        __DIR__ . '/landing/index.html',
        __DIR__ . '/public/landing/index.html',
        __DIR__ . '/public/landing/web.html'
    ];
    foreach ($candidates as $path) {
        if (file_exists($path)) { readfile($path); return; }
    }
    http_response_code(500);
    echo 'Internal Server Error';
}
