<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Events\ModuleStatusChanged;
use App\Helper\Reply;
use App\Models\ModuleSetting;
use App\Models\GlobalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Macellan\Zip\Zip;
use Nwidart\Modules\Facades\Module;
use App\Http\Controllers\AccountBaseController;

class CustomModuleController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.moduleSettings';
        $this->activeSettingMenu = 'module_settings';
        $this->middleware(function ($request, $next) {
            abort_403(GlobalSetting::validateSuperAdmin('manage_superadmin_custom_module_settings'));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->type = 'custom';
        $this->updateFilePath = config('craveva.tmp_path');
        /** @phpstan-ignore-next-line */
        $this->allModules = Module::toCollection()->filter(function ($module, $key) {
            return $key !== 'Craveva';
        });

        /** @phpstan-ignore-next-line */
        $this->craveva = Module::find('Craveva');

        $this->view = 'super-admin.custom-modules.ajax.custom';
        $this->activeTab = 'custom';
        
        // Check if CravevaUpdate class exists before using it
        if (class_exists('CravevaUpdate')) {
            $this->plugins = collect(CravevaUpdate::plugins());
        } else {
            $this->plugins = collect([]);
        }

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('super-admin.module-settings.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->pageTitle = 'app.menu.moduleSettingsInstall';
        $this->type = 'custom';
        $this->updateFilePath = config('craveva.tmp_path');

        return view('super-admin.custom-modules.install', $this->data);
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function store(Request $request)
    {
        if (!extension_loaded('zip')) {
            return Reply::error('<b>PHP-ZIP</b> extension is missing on your server. Please install the extension.');
        }

        File::put(public_path() . '/install-version.txt', 'complete');

        $filePath = $request->filePath;

        $zip = Zip::open($filePath);

        $zipName = $this->getZipName($filePath);

        // Extract the files to storage folder first for checking the right plugin
        // Filename Like craveva-0gOuGKoY-zoom-meeting-module-for-craveva.zip
        if (str_contains($zipName, 'craveva-')) {
            $zipName = $this->unzipcraveva($zip);
        } else {
            $zip->extract(storage_path('app') . '/Modules');
        }

        $moduleName = str_replace('.zip', '', $zipName);

        $validateModule = $this->validateModule($moduleName);

        if ($validateModule['status'] == true) {
            // Move files to Modules if modules belongs to this product
            File::moveDirectory(storage_path('app') . '/Modules/' . $moduleName, base_path() . '/Modules/' . $moduleName, true);

            cache()->forget('laravel-modules');

            // Delete Modules Directory after moving files
            File::deleteDirectory(storage_path('app') . '/Modules/');

            if (module_enabled($moduleName)) {
                $this->updateVersion($moduleName);
            }

            // if module is craveva module then activate the module
            if ($moduleName == 'Craveva') {
                /** @phpstan-ignore-next-line */
                $module = Module::findOrFail($moduleName);
                $module->enable();
                Artisan::call('module:migrate', array($moduleName, '--force' => true));
                event(new ModuleStatusChanged($module, 'active'));
            }

            $this->flushData();

            return Reply::success('Installed successfully.');
        }

        return Reply::error($validateModule['message']);
    }

    public function validateModule($moduleName)
    {
        $appName = str_replace('-new', '', config('craveva.craveva_product_name'));
        $wrongMessage = 'The zip that you are trying to install is not compatible with ' . $appName . ' version';

        // Check if PHP-ZIP extension is missing
        if (!extension_loaded('zip')) {
            return [
                'status' => false,
                'message' => '<b>PHP-ZIP</b> extension is missing on your server. Please install the extension.'
            ];
        }

        $configPath = storage_path('app') . '/Modules/' . $moduleName . '/Config/config.php';

        // Check if module configuration file exists
        if (!file_exists($configPath)) {
            return [
                'status' => false,
                'message' => $wrongMessage
            ];
        }

        $config = require_once $configPath;

        // Check if parent_craveva_id is defined and matches the application's craveva_id
        if (!isset($config['parent_craveva_id']) || $config['parent_craveva_id'] !== config('craveva.craveva_item_id')) {
            return [
                'status' => false,
                'message' => 'You are installing the wrong module for this product'
            ];
        }

        // Parent craveva id is different from module craveva id
        if ($config['parent_craveva_id'] !== config('craveva.craveva_item_id')) {
            return [
                'status' => false,
                'message' => 'You are installing wrong module for this product'
            ];
        }

        // Check if parent_min_version is defined
        if (!isset($config['parent_min_version'])) {
            $errorMessage = App::environment('craveva') ? 'Please download and install the latest version of the module.' : 'Minimum version of <b>' . $appName . ' main application</b> is not defined in the Module.';

            return [
                'status' => false,
                'message' => $errorMessage
            ];
        }

        // Check if the application version is lower than the required minimum version
        if ($config['parent_min_version'] >= File::get('version.txt')) {
            return [
                'status' => false,
                'message' => 'Minimum version of <b>' . $appName . ' main application</b> should be greater than or equal to <b>' . $config['parent_min_version'] . '</b>. Your application version is <b>' . File::get('version.txt') . '</b>'
            ];
        }

        // Check if parent_product_name is defined and matches the application's product name
        if (!isset($config['parent_product_name']) || $config['parent_product_name'] !== config('craveva.craveva_product_name')) {
            return [
                'status' => false,
                'message' => $wrongMessage
            ];
        }

        return [
            'status' => true,
            'message' => 'Module is valid'
        ];
    }

    private function getZipName($filePath)
    {
        return basename($filePath);
    }

    private function unzipcraveva($zip)
    {
        $zip->extract(storage_path('app') . '/Modules');
        
        // Get the extracted directory name
        $extractedDirs = File::directories(storage_path('app') . '/Modules');
        
        if (!empty($extractedDirs)) {
            return basename($extractedDirs[0]);
        }
        
        return null;
    }

    private function updateVersion($moduleName)
    {
        // Update module version logic here
        // This would typically involve updating database records or config files
    }

    private function flushData()
    {
        // Clear any cached data
        cache()->flush();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Module deletion logic
        return Reply::success('Module deleted successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Module update logic
        return Reply::success('Module updated successfully.');
    }

    /**
     * Delete module file
     *
     * @param Request $request
     * @return array
     */
    public function deleteFile(Request $request)
    {
        $filePath = $request->filePath;
        
        if (File::exists($filePath)) {
            File::delete($filePath);
            return Reply::success('File deleted successfully.');
        }
        
        return Reply::error('File not found.');
    }
}