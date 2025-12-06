<?php

use Illuminate\Support\Facades\Route;
use Modules\Craveva\Http\Controllers\CravevaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Admin routes
Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {

    Route::group(
        ['prefix' => 'settings'],
        function () {
            Route::post('install-craveva-module', [CravevaController::class, 'installCravevaModule'])->name('install-craveva-module');
            Route::post('add-craveva-module-purchase-code', [CravevaController::class, 'addCravevaModulePurchaseCode'])->name('add-craveva-module-purchase-code');
        }
    );

});
