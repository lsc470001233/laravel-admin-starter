<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'account' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'account.required' => '请输入用户名或邮箱',
            'password.required' => '请输入登录密码',
            'password.min' => '密码长度不能少于 6 位',
        ]);

        $account = trim($validated['account']);
        $field = filter_var($account, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::query()->where($field, $account)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->failResponse($request, '用户名/邮箱或密码错误');
        }

        if ($user->status !== 1) {
            return $this->failResponse($request, '该账号已被禁用，请联系管理员');
        }

        if (! $user->is_admin) {
            return $this->failResponse($request, '该账号没有后台管理权限');
        }

        Auth::login($user, (bool) $request->boolean('remember'));
        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'code' => 0,
                'message' => '登录成功',
                'redirect' => route('admin.index', [], false),
            ]);
        }

        return redirect()->intended(route('admin.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function failResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['code' => 1, 'message' => $message], 422);
        }

        return back()->withErrors(['account' => $message])->onlyInput('account');
    }
}
