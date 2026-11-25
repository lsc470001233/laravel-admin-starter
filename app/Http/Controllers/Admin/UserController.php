<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index');
    }

    public function list(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->integer('limit', 20), 1), 100);
        $keyword = trim((string) $request->input('keyword', ''));
        $status = $request->input('status', '');

        $query = User::query()->orderByDesc('id');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('username', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $page = $query->paginate($limit);

        return response()->json([
            'code' => 0,
            'msg' => '',
            'count' => $page->total(),
            'data' => $page->getCollection()->map(fn (User $u) => [
                'id' => $u->id,
                'username' => $u->username,
                'name' => $u->name,
                'email' => $u->email,
                'status' => $u->status,
                'is_admin' => $u->is_admin,
                'last_login_at' => $u->last_login_at?->format('Y-m-d H:i:s') ?? '-',
                'last_login_ip' => $u->last_login_ip ?? '-',
                'created_at' => $u->created_at?->format('Y-m-d H:i') ?? '-',
            ])->values(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => null]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:64', 'alpha_dash', 'unique:users,username'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:64'],
            'status' => ['required', 'in:0,1'],
            'is_admin' => ['nullable', 'boolean'],
        ], [
            'username.required' => '请输入用户名',
            'username.unique' => '该用户名已被占用，请更换',
            'username.alpha_dash' => '用户名只能包含字母、数字、下划线和短横线',
            'email.required' => '请输入邮箱地址',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '该邮箱已被占用',
            'password.required' => '请设置初始密码',
            'password.min' => '密码长度不能少于 6 位',
        ]);

        User::query()->create([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => (int) $data['status'],
            'is_admin' => (bool) ($data['is_admin'] ?? false),
        ]);

        return response()->json(['code' => 0, 'message' => '用户创建成功']);
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:64', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'status' => ['required', 'in:0,1'],
            'is_admin' => ['nullable', 'boolean'],
        ], [
            'username.unique' => '该用户名已被占用',
            'email.unique' => '该邮箱已被占用',
        ]);

        if ($user->username === 'admin' && ((int) $data['status'] === 0 || ! ($data['is_admin'] ?? false))) {
            return response()->json(['code' => 1, 'message' => '不能禁用默认超级管理员或取消其管理权限'], 422);
        }

        $user->update([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => (int) $data['status'],
            'is_admin' => (bool) ($data['is_admin'] ?? false),
        ]);

        return response()->json(['code' => 0, 'message' => '用户信息已更新']);
    }

    public function passwordForm(User $user): View
    {
        return view('admin.users.password', ['user' => $user]);
    }

    public function updatePassword(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:6', 'max:64', 'confirmed'],
        ], [
            'password.required' => '请输入新密码',
            'password.min' => '密码长度不能少于 6 位',
            'password.confirmed' => '两次输入的密码不一致',
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return response()->json(['code' => 0, 'message' => '密码已更新']);
    }

    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        if ($user->username === 'admin' || $user->id === $request->user()->id) {
            return response()->json(['code' => 1, 'message' => '不能禁用当前登录账号或默认管理员'], 422);
        }

        $user->update(['status' => $user->status === 1 ? 0 : 1]);

        return response()->json([
            'code' => 0,
            'message' => $user->status === 1 ? '已启用该用户' : '已禁用该用户',
            'status' => $user->status,
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->username === 'admin' || $user->id === $request->user()->id) {
            return response()->json(['code' => 1, 'message' => '不能删除默认管理员或当前登录账号'], 422);
        }

        $user->delete();

        return response()->json(['code' => 0, 'message' => '用户已删除']);
    }
}
