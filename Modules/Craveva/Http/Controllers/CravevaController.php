<?php

namespace Modules\Craveva\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Modules\Craveva\Entities\CravevaSetting;
use Modules\Craveva\Entities\CravevaModuleInstall;
use \Nwidart\Modules\Facades\Module;

class CravevaController extends AccountBaseController
{

    public function installCravevaModule(Request $request)
    {
        $modulePath = getCravevaModulesPath() . '/' . $request->module;

        if (!file_exists($modulePath)) {
            return Reply::error(__('craveva::app.moduleIsNotAvailable', ['module' => $request->module]));
        }

        $moduleInstallationPath = base_path() . '/Modules/' . $request->module;

        File::copyDirectory($modulePath, $moduleInstallationPath);

        cache()->forget('laravel-modules');

        $appModule = Module::findOrFail($request->module);
        $appModule->enable();

        Artisan::call('module:migrate', array($request->module, '--force' => true));

        return Reply::success(__('craveva::app.moduleIsInstalling', ['module' => $request->module]));
    }

    public function addCravevaModulePurchaseCode(Request $request)
    {
        $appModule = Module::findOrFail($request->module);

        CravevaModuleInstall::updateOrCreate([
            'module_name' => $request->module,
        ], [
            'version' => File::get($appModule->getPath() . '/version.txt'),
        ]);

        if (config(strtolower($request->module) . '.setting')) {
            $cravevaSetting = CravevaSetting::first();
            $fetchSetting = config(strtolower($request->module) . '.setting')::first();
            if ($fetchSetting && $cravevaSetting?->purchase_code && !$fetchSetting->purchase_code) {
                $fetchSetting->purchase_code = $cravevaSetting->purchase_code;
                $fetchSetting->supported_until = $cravevaSetting->supported_until;
                $fetchSetting->save();
            }
        }

        $user = auth()->id();
        cache()->flush();
        session()->flush();
        auth()->loginUsingId($user);

        return Reply::success(__('craveva::app.moduleIsInstalled', ['module' => $request->module]));
    }

}
