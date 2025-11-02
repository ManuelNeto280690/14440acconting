<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display the admin settings page.
     */
    public function index()
    {
        $settings = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'tenant_limit' => config('app.tenant_limit', 100),
            'max_file_size' => config('app.max_file_size', 10240), // KB
            'allowed_file_types' => config('app.allowed_file_types', 'pdf,jpg,jpeg,png,doc,docx'),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'cache_enabled' => config('cache.default') !== 'array',
            'debug_mode' => config('app.debug'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update system settings.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
            'tenant_limit' => 'required|integer|min:1|max:1000',
            'max_file_size' => 'required|integer|min:1024|max:102400', // 1MB to 100MB
            'allowed_file_types' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Update configuration values in cache
            $settings = $request->only([
                'app_name',
                'app_url', 
                'mail_from_address',
                'mail_from_name',
                'tenant_limit',
                'max_file_size',
                'allowed_file_types'
            ]);

            foreach ($settings as $key => $value) {
                Cache::forever("settings.{$key}", $value);
            }

            // Clear config cache to apply changes
            \Artisan::call('config:clear');

            return redirect()->route('admin.settings')
                ->with('success', 'Settings updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update settings: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');

            return redirect()->route('admin.settings')
                ->with('success', 'Cache cleared successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance()
    {
        try {
            if (app()->isDownForMaintenance()) {
                \Artisan::call('up');
                $message = 'Maintenance mode disabled.';
            } else {
                \Artisan::call('down', ['--secret' => 'admin-access']);
                $message = 'Maintenance mode enabled.';
            }

            return redirect()->route('admin.settings')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to toggle maintenance mode: ' . $e->getMessage());
        }
    }

    /**
     * Get system information.
     */
    public function systemInfo()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
            'storage_driver' => config('filesystems.default'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];

        return response()->json($info);
    }
}