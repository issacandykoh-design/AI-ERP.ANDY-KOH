<?php

namespace App\Http\Controllers;

use App\Models\Flag;
use App\Helper\Reply;
use App\Models\GlobalSetting;
use Illuminate\Http\Request;
use App\Models\LanguageSetting;
use App\Models\TranslateSetting;
use App\Services\AiTranslationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Lang;
use App\Http\Requests\Admin\Language\StoreRequest;
use App\Http\Requests\Admin\Language\UpdateRequest;
use Barryvdh\TranslationManager\Models\Translation;
use App\Http\Requests\Admin\Language\AutoTranslateRequest;

class LanguageSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.languageSettings';
        $this->activeSettingMenu = 'language_settings';
        $this->langPath = base_path() . '/resources/lang';
        $this->middleware(function ($request, $next) {
            abort_403(((user()->permission('manage_language_setting') !== 'all') && GlobalSetting::validateSuperAdmin('manage_superadmin_language_settings')));
            return $next($request);
        });
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        config(['app.debug' => true]);
        if (request()->has('debug')) {
            return 'languages_count=' . LanguageSetting::query()->count();
        }
        $this->languages = LanguageSetting::all();
        $aiService = new AiTranslationService();
        $this->aiConfigured = $aiService->isConfigured();
        if (request()->has('debug_error')) {
            try {
                return view('language-settings.index', $this->data);
            } catch (\Throwable $e) {
                return response('exception: ' . $e->getMessage(), 500);
            }
        }
        return view('language-settings.index', $this->data);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return array
     * @throws \Open\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    // phpcs:ignore
    public function update(Request $request, $id)
    {
        $setting = LanguageSetting::findOrFail($request->id);

        if ($request->has('status')) {
            $setting->status = $request->status;
        }

        $setting->save();


        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * @param UpdateRequest $request
     * @param int $id
     * @return array
     */
    // phpcs:ignore
    public function updateData(UpdateRequest $request, $id)
    {
        $setting = LanguageSetting::findOrFail($request->id);

        $oldLangExists = File::exists($this->langPath.'/'.$setting->language_code);

        if($oldLangExists){
            // check and create lang folder
            $langExists = File::exists($this->langPath . '/' . $request->language_code);

            if (!$langExists) {
                // update lang folder name
                File::move($this->langPath . '/' . $setting->language_code, $this->langPath . '/' . $request->language_code);

                Translation::where('locale', $setting->language_code)->get()->map(function ($translation) {
                    $translation->delete();
                });
            }
        }

        $setting->language_name = $request->language_name;
        $setting->language_code = $request->language_code;
        $setting->flag_code = strtolower($request->flag);
        $setting->status = $request->status;
        $setting->is_rtl = $request->is_rtl;
        $setting->save();


        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreRequest $request)
    {
        // check and create lang folder
        $langExists = File::exists($this->langPath . '/' . $request->language_code);

        if (!$langExists) {
            File::makeDirectory($this->langPath . '/' . $request->language_code);
        }

        $setting = new LanguageSetting();
        $setting->language_name = $request->language_name;
        $setting->language_code = $request->language_code;
        $setting->flag_code = $request->flag;
        $setting->status = $request->status;
        $setting->is_rtl = $request->is_rtl;
        $setting->save();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        $this->flags = Flag::get();

        return view('language-settings.create-language-settings-modal', $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function autoTranslate(Request $request)
    {
        $this->translateSetting = TranslateSetting::first();
        $aiService = new AiTranslationService();
        $this->aiConfigured = $aiService->isConfigured();
        return view('language-settings.auto-translate-modal', $this->data);
    }

    public function autoTranslateUpdate(AutoTranslateRequest $request)
    {
        $translateSetting = TranslateSetting::first();
        $translateSetting->update($request->validated());

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Request $request, $id)
    {
        $this->languageSetting = LanguageSetting::findOrFail($id);
        $this->flags = Flag::get();

        return view('language-settings.edit-language-settings-modal', $this->data);
    }

    /**
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        $language = LanguageSetting::findOrFail($id);
        $setting = companyOrGlobalSetting();

        if ($language->language_code == $setting->locale) {
            $setting->locale = 'en';
            $setting->last_updated_by = $this->user->id;
            $setting->save();
            session()->forget('user');
        }

        $language->destroy($id);

        $langExists = File::exists($this->langPath . '/' . $language->language_code);

        if ($langExists) {
            File::deleteDirectory($this->langPath . '/' . $language->language_code);
        }

        if (Schema::hasTable('ltm_translations')) {
            DB::statement('DELETE FROM ltm_translations where locale = "'.$language->language_code.'"');
        }

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function fixTranslation()
    {
        Artisan::call('translations:reset');
        Artisan::call('translations:import');
        return Reply::success(__('modules.languageSettings.fixTranslationSuccess'));
    }

    public function createEnLocale()
    {
        // copy eng folder from resources/lang to resources/lang/en
        File::copyDirectory($this->langPath . '/eng', $this->langPath . '/en');

        // copy eng.json file from resources/lang to resources/lang/en.json
        File::copy($this->langPath . '/eng.json', $this->langPath . '/en.json');

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Translate entire language using AI
     */
    public function aiTranslateLanguage(Request $request)
    {
        $request->validate([
            'target_language_id' => 'required|exists:language_settings,id',
            'source_language' => 'required|string',
        ]);

        $targetLanguage = LanguageSetting::findOrFail($request->target_language_id);
        $sourceLanguage = $request->source_language;

        $aiService = new AiTranslationService();

        if (!$aiService->isConfigured()) {
            return Reply::error('AI Translation is not configured. Please set up OpenRouter API key in AI Settings.');
        }

        // Get all translations from source language
        $translations = $this->getAllTranslations($sourceLanguage);
        $total = count($translations);
        $translated = 0;
        $errors = 0;

        // Process in batches
        $batchSize = 50;
        $batches = array_chunk($translations, $batchSize, true);

        foreach ($batches as $batch) {
            $translatedBatch = $aiService->translateBatch($batch, $targetLanguage->language_code, $sourceLanguage);

            foreach ($translatedBatch as $key => $translatedText) {
                if ($translatedText) {
                    $this->saveTranslation($targetLanguage->language_code, $key, $translatedText);
                    $translated++;
                } else {
                    $errors++;
                }
            }
        }

        // Export translations to files (if translation manager is available)
        if (Schema::hasTable('ltm_translations')) {
            try {
                Artisan::call('translations:export', [
                    'group' => '*',
                ]);
            } catch (\Exception $e) {
                // If export fails, translations are still saved in database
                \Log::info('Translation export failed: ' . $e->getMessage());
            }
        }

        return Reply::success("Translation completed! Translated {$translated} of {$total} strings. Errors: {$errors}");
    }

    /**
     * Get all translations from a language
     */
    private function getAllTranslations(string $locale): array
    {
        $translations = [];

        // Try database first (translation manager)
        if (Schema::hasTable('ltm_translations')) {
            try {
                $dbTranslations = Translation::where('locale', $locale)->get();
                foreach ($dbTranslations as $translation) {
                    $key = $translation->group . '.' . $translation->key;
                    $translations[$key] = $translation->value;
                }
                
                if (!empty($translations)) {
                    return $translations;
                }
            } catch (\Exception $e) {
                \Log::error('Error loading translations from database: ' . $e->getMessage());
            }
        }

        // Fallback: read from files
        $langPath = $this->langPath . '/' . ($locale === 'en' ? 'eng' : $locale);
        if (File::exists($langPath)) {
            foreach (File::allFiles($langPath) as $file) {
                $group = $file->getFilenameWithoutExtension();
                try {
                    $fileTranslations = Lang::getLoader()->load($locale === 'en' ? 'eng' : $locale, $group);
                    if (is_array($fileTranslations)) {
                        foreach ($this->flattenTranslations($fileTranslations) as $key => $value) {
                            $fullKey = $group . '.' . $key;
                            $translations[$fullKey] = $value;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error("Error loading translation file {$file}: " . $e->getMessage());
                }
            }
        }

        return $translations;
    }

    /**
     * Flatten nested translation array
     */
    private function flattenTranslations(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '.' . $key : $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenTranslations($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }

    /**
     * Save translation to database
     */
    private function saveTranslation(string $locale, string $key, string $value): void
    {
        if (!Schema::hasTable('ltm_translations')) {
            return;
        }

        try {
            $parts = explode('.', $key, 2);
            $group = $parts[0] ?? 'app';
            $translationKey = $parts[1] ?? $key;

            Translation::updateOrCreate(
                [
                    'locale' => $locale,
                    'group' => $group,
                    'key' => $translationKey,
                ],
                [
                    'value' => $value,
                ]
            );
        } catch (\Exception $e) {
            \Log::error("Error saving translation {$key} for locale {$locale}: " . $e->getMessage());
        }
    }

    /**
     * Test AI translation connection
     */
    public function testAiTranslation()
    {
        $aiService = new AiTranslationService();

        if (!$aiService->isConfigured()) {
            return Reply::error('AI Translation is not configured. Please set up OpenRouter API key in AI Settings.');
        }

        $testResult = $aiService->testConnection();

        if ($testResult) {
            return Reply::success('AI Translation is working correctly!');
        }

        return Reply::error('AI Translation test failed. Please check your OpenRouter API key in AI Settings.');
    }

    /**
     * Get translation progress for a language
     */
    public function getTranslationProgress(Request $request, $id)
    {
        $language = LanguageSetting::findOrFail($id);
        $sourceLanguage = 'en'; // Default source

        $sourceTranslations = $this->getAllTranslations($sourceLanguage);
        $targetTranslations = $this->getAllTranslations($language->language_code);

        $total = count($sourceTranslations);
        $translated = 0;

        foreach ($sourceTranslations as $key => $value) {
            if (isset($targetTranslations[$key]) && !empty($targetTranslations[$key])) {
                $translated++;
            }
        }

        $percentage = $total > 0 ? round(($translated / $total) * 100, 2) : 0;

        return Reply::dataOnly([
            'total' => $total,
            'translated' => $translated,
            'missing' => $total - $translated,
            'percentage' => $percentage,
        ]);
    }

}
