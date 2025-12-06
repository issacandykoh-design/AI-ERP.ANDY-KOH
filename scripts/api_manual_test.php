<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Auth::logout();

function run($method, $uri, $headers = [], $json = null) {
    global $app;
    $server = [];
    foreach ($headers as $k => $v) {
        $server['HTTP_' . strtoupper(str_replace('-', '_', $k))] = $v;
    }
    $content = null;
    if (is_array($json)) {
        $content = json_encode($json);
    } elseif (is_string($json)) {
        $content = $json;
    }
    $req = Illuminate\Http\Request::create($uri, $method, [], [], [], $server, $content);
    if ($content !== null) {
        $req->headers->set('Content-Type', 'application/json');
    }
    $res = $app->handle($req);
    echo $res->getStatusCode() . "\n";
    echo $res->getContent() . "\n";
}

$token = '2|UAT-Plain-Token-2';

run('GET', '/_ignition/health-check');
run('GET', '/api/products/1/catalog');
run('POST', '/api/products/1/bundles/1/price', ['Accept' => 'application/json'], ['option_item_ids' => [1]]);
run('GET', '/api/auth/me', ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json']);
run('POST', '/api/products/1/variants/generate', ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json'], []);
run('PUT', '/api/products/1/bundles/1/status', ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json'], ['is_active' => false]);

Illuminate\Support\Facades\Auth::loginUsingId(1);
run('GET', '/account/settings/menu-customization');
