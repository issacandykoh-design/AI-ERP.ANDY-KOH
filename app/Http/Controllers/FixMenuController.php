<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\CustomMenuSetting;
use Illuminate\Http\Request;

class FixMenuController extends Controller
{
    /**
     * Emergency fix: Reset all menu visibility to visible
     * This can be accessed directly via URL when settings menu is hidden
     */
    public function resetAllMenus()
    {
        // Check if user is logged in
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in first');
        }

        $companyId = auth()->user()->company_id;

        if (!$companyId) {
            return response('No company ID found. Please contact support.', 400);
        }

        // Set all menu items to visible
        $updated = CustomMenuSetting::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->update(['is_visible' => true]);

        // Clear all caches
        \Cache::flush();
        \Artisan::call('view:clear');
        \Artisan::call('config:clear');

        // Return a simple HTML page with success message
        return response("
            <html>
                <head>
                    <title>Menu Fix Complete</title>
                    <style>
                        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                        .success { color: green; font-size: 24px; margin-bottom: 20px; }
                        .info { color: #666; margin: 20px 0; }
                        button { padding: 10px 20px; font-size: 16px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 4px; }
                        button:hover { background: #0056b3; }
                        a { color: #007bff; text-decoration: none; }
                    </style>
                </head>
                <body>
                    <div class='success'>✓ Success!</div>
                    <div class='info'>Successfully reset {$updated} menu items to visible.</div>
                    <div class='info'>Please refresh your browser to see the changes.</div>
                    <button onclick='window.location.reload()'>Refresh Page</button>
                    <br><br>
                    <a href='/account/dashboard'>Go to Dashboard</a>
                </body>
            </html>
        ");
    }
}

