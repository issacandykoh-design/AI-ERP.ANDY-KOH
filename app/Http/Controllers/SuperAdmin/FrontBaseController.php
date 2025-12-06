<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin\FooterMenu;
use App\Models\SuperAdmin\FrontDetail;
use App\Models\SuperAdmin\FrontMenu;
use App\Models\SuperAdmin\FrontWidget;
use App\Models\SuperAdmin\TrFrontDetail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

class FrontBaseController extends Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->showInstall();
        $this->middleware(function ($request, $next) {

            $this->frontDetail = FrontDetail::first();
            if (!$this->frontDetail) {
                $this->frontDetail = new FrontDetail();
                $this->frontDetail->locale = 'en';
            }

            if (!$this->frontDetail->locale) {
                $this->frontDetail->locale = 'en';
            }
            $this->languages = language_setting();
            $this->global = $this->globalSetting = $this->setting = global_setting();

            $this->locale = $this->frontDetail->locale ?? 'en';

            if (session()->has('language')) {
                $this->locale = session('language');
            }

            App::setLocale($this->locale);
            Carbon::setLocale($this->locale);
            setlocale(LC_TIME, $this->locale . '_' . strtoupper($this->locale));

            $this->enLocaleLanguage = language_setting_locale('en');
            $this->localeLanguage = $this->locale != 'en' ? language_setting_locale($this->locale) : $this->enLocaleLanguage;
            $this->localeLanguage = $this->localeLanguage ?: $this->enLocaleLanguage;

            // 安全检查：确保语言设置存在
            if (!$this->localeLanguage) {
                // 如果没有找到语言设置，创建默认的英语设置
                $this->localeLanguage = \App\Models\LanguageSetting::firstOrCreate(
                    ['language_code' => 'en'],
                    [
                        'language_name' => 'English',
                        'flag_code' => 'en',
                        'status' => 'enabled',
                        'is_rtl' => false
                    ]
                );
                $this->enLocaleLanguage = $this->localeLanguage;
            }

            if (!$this->enLocaleLanguage) {
                $this->enLocaleLanguage = $this->localeLanguage;
            }

            $this->footerSettings = FooterMenu::whereNotNull('slug')
                ->where('private', 0)
                ->where('language_setting_id', $this->localeLanguage->id)
                ->get();

            $this->footerSettings = $this->footerSettings->count() > 0 ? $this->footerSettings : FooterMenu::whereNotNull('slug')->where('private', 0)
                ->where('language_setting_id', $this->enLocaleLanguage->id)
                ->get();

            $this->frontMenu = FrontMenu::where('language_setting_id', $this->localeLanguage->id)->first();
            $this->frontMenu = $this->frontMenu ?: FrontMenu::where('language_setting_id', $this->enLocaleLanguage->id)->first();
            
            // 如果仍然没有找到FrontMenu记录，创建默认记录
            if (!$this->frontMenu) {
                $this->frontMenu = FrontMenu::create([
                    'language_setting_id' => $this->enLocaleLanguage->id,
                    'home' => 'Home',
                    'price' => 'Pricing',
                    'contact' => 'Contact',
                    'feature' => 'Features',
                    'get_start' => 'Get Started',
                    'login' => 'Login',
                    'contact_submit' => 'Submit Enquiry'
                ]);
            }

            $this->frontWidgets = FrontWidget::all();

            $this->detail = $this->frontDetail;

            $this->trFrontDetail = TrFrontDetail::where('language_setting_id', $this->localeLanguage->id)->first();
            $this->trFrontDetail = $this->trFrontDetail ?: TrFrontDetail::where('language_setting_id', $this->enLocaleLanguage->id)->first();
            
            // 如果仍然没有找到TrFrontDetail记录，创建默认记录
            if (!$this->trFrontDetail) {
                $this->trFrontDetail = TrFrontDetail::create([
                    'language_setting_id' => $this->enLocaleLanguage->id,
                    'header_title' => 'HR, CRM, and Project Management System',
                    'header_description' => 'The simplest and most powerful way to collaborate with your team.',
                    'task_management_title' => 'Task Management',
                    'task_management_detail' => 'Manage your projects and talent in one system for empowered teams, satisfied clients, and increased profitability.',
                    'manage_bills_title' => 'Manage All Bills',
                    'manage_bills_detail' => 'Automate billing and revenue recognition to streamline the contract-to-cash cycle.',
                    'favourite_apps_title' => 'Integrate with Favorite Apps',
                    'favourite_apps_detail' => 'Our app integrates with other third-party apps for added advantage.',
                    'cta_title' => 'Easier Business Management',
                    'cta_detail' => 'Our experts will show you how our app can streamline your team\'s work.',
                    'client_title' => 'Trusted by the World\'s Best Teams',
                    'client_detail' => 'Over 700 people use our product.',
                    'testimonial_title' => 'Loved by Businesses and Individuals Worldwide',
                    'faq_title' => 'FAQs',
                    'footer_copyright_text' => 'Copyright © 2020. All Rights Reserved',
                    'feature_title' => 'Team Communication for the 21st Century',
                    'price_title' => 'Affordable Pricing',
                    'price_description' => 'craveva for Teams is a single workspace for your small- to medium-sized company or team.'
                ]);
            }

            // ACCOUNT SETUP REDIRECT
            $userTotal = User::count();

            if ($userTotal == 0 && !module_enabled('Subdomain')) {
                return redirect()->route('login');
            }

            return $next($request);
        });

    }

    public function isLegal()
    {
        return true;
    }

}
