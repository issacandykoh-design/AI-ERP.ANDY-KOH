<?php

namespace Database\Seeders;

use App\Models\ModuleSwitch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ModuleSwitchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modulesPath = base_path('Modules');
        
        if (!File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            $moduleJsonPath = $modulePath . '/module.json';

            if (File::exists($moduleJsonPath)) {
                $moduleData = json_decode(File::get($moduleJsonPath), true);
                
                if ($moduleData) {
                    ModuleSwitch::updateOrCreate(
                        ['module_name' => $moduleName],
                        [
                            'display_name' => $moduleData['name'] ?? $moduleName,
                            'description' => $moduleData['description'] ?? '',
                            'version' => $moduleData['version'] ?? '1.0.0',
                            'is_enabled' => $moduleData['active'] ?? true,
                        ]
                    );
                }
            } else {
                // If no module.json exists, create a basic entry
                ModuleSwitch::updateOrCreate(
                    ['module_name' => $moduleName],
                    [
                        'display_name' => $moduleName,
                        'description' => 'Module: ' . $moduleName,
                        'version' => '1.0.0',
                        'is_enabled' => true,
                    ]
                );
            }
        }

        $this->command->info('Module switches seeded successfully.');
    }
}