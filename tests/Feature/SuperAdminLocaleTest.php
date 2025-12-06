<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Support\Facades\Route;

class SuperAdminLocaleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Reset and migrate sqlite schema fresh for reliable tests
        \Artisan::call('migrate:fresh', ['--force' => true]);
    }

    public function test_superadmin_profile_locale_updates_session()
    {
        $auth = new UserAuth();
        $auth->email = 'superadmin@example.com';
        $auth->password = bcrypt('password123');
        $auth->save();

        $user = new User();
        $user->name = 'Superadmin';
        $user->email = 'superadmin@example.com';
        $user->user_auth_id = $auth->id;
        $user->is_superadmin = 1;
        $user->status = 'active';
        $user->locale = 'en';
        $user->rtl = 0;
        $user->save();

        $this->actingAs($user);

        $payload = [
            'name' => 'Superadmin',
            'email' => 'superadmin@example.com',
            'locale' => 'fr',
            'rtl' => 0,
            'email_notifications' => 1,
            'redirect_url' => '',
        ];

        $response = $this->post(route('profile.update', [$user->id]), $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $this->assertEquals('fr', session('language'));
        $this->assertEquals('fr', session('locale'));

        // Subsequent authenticated request should see the new locale applied
        $r = $this->get(route('language-settings.index').'?debug=1');
        $r->assertStatus(200);
    }
}
