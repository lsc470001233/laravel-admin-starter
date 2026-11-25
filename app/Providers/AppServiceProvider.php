<?php

namespace App\Providers;

use App\Http\Controllers\Admin\SettingController;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('admin.*', function ($view) {
            $theme = 'sage';
            try {
                if (Schema::hasTable('system_settings')) {
                    $appearance = SystemSetting::getGroup('appearance');
                    $saved = $appearance['admin_theme'] ?? 'sage';
                    if (array_key_exists($saved, SettingController::THEMES)) {
                        $theme = $saved;
                    }
                }
            } catch (Throwable) {
                $theme = 'sage';
            }

            $view->with('adminTheme', $theme);
        });
    }
}
