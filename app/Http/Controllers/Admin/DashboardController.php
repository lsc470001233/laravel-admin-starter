<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DictItem;
use App\Models\DictType;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.layouts.app');
    }

    public function dashboard(): View
    {
        $smtp = SystemSetting::getGroup('smtp');

        return view('admin.dashboard', [
            'stats' => [
                'users_total' => User::query()->count(),
                'users_active' => User::query()->where('status', 1)->count(),
                'dict_types' => DictType::query()->count(),
                'dict_items' => DictItem::query()->count(),
                'smtp_configured' => ! empty($smtp['smtp_host']) && ! empty($smtp['smtp_username']),
            ],
            'env' => [
                'laravel' => app()->version(),
                'php' => PHP_VERSION,
                'db' => config('database.connections.mysql.database'),
                'server_time' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
