<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DictController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// 前台主页
Route::get('/', [HomeController::class, 'index'])->name('home');

// 后台认证路由
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // 后台主框架 & 工作台
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        // 用户管理（右侧抽屉新增/编辑 + 居中弹窗改密）
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/list', [UserController::class, 'list'])->name('list');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::get('/{user}/password', [UserController::class, 'passwordForm'])->name('password_form');
            Route::put('/{user}/password', [UserController::class, 'updatePassword'])->name('password_update');
            Route::patch('/{user}/status', [UserController::class, 'toggleStatus'])->name('status');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });

        // 数据字典（上下主从分屏：上方字典类型，下方字典项）
        Route::prefix('dicts')->name('dicts.')->group(function () {
            Route::get('/', [DictController::class, 'index'])->name('index');

            // 主表：字典类型
            Route::get('/types/list', [DictController::class, 'typeList'])->name('types.list');
            Route::get('/types/create', [DictController::class, 'createType'])->name('types.create');
            Route::post('/types', [DictController::class, 'storeType'])->name('types.store');
            Route::get('/types/{dictType}/edit', [DictController::class, 'editType'])->name('types.edit');
            Route::put('/types/{dictType}', [DictController::class, 'updateType'])->name('types.update');
            Route::delete('/types/{dictType}', [DictController::class, 'destroyType'])->name('types.destroy');

            // 从表：字典项
            Route::get('/types/{dictType}/items', [DictController::class, 'itemList'])->name('items.list');
            Route::get('/types/{dictType}/items/create', [DictController::class, 'createItem'])->name('items.create');
            Route::post('/types/{dictType}/items', [DictController::class, 'storeItem'])->name('items.store');
            Route::get('/items/{dictItem}/edit', [DictController::class, 'editItem'])->name('items.edit');
            Route::put('/items/{dictItem}', [DictController::class, 'updateItem'])->name('items.update');
            Route::delete('/items/{dictItem}', [DictController::class, 'destroyItem'])->name('items.destroy');
        });

        // 系统设置（主题配色 + SMTP 配置 + 发送测试邮件）
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/theme', [SettingController::class, 'saveTheme'])->name('theme.save');
            Route::post('/smtp', [SettingController::class, 'saveSmtp'])->name('smtp.save');
            Route::get('/smtp/test-modal', [SettingController::class, 'testMailModal'])->name('smtp.test_modal');
            Route::post('/smtp/test', [SettingController::class, 'sendTestMail'])->name('smtp.test');
        });
    });
});
