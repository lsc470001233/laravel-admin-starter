<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SettingController extends Controller
{
    public const THEMES = [
        'sage' => [
            'name' => '护眼青灰',
            'desc' => 'Layui 经典绿现代升级，低饱和青灰久看不累',
            'header' => '#ffffff',
            'side' => '#f5f9f8',
            'primary' => '#0f766e',
            'canvas' => '#eff4f3',
            'dark_side' => false,
        ],
        'blue' => [
            'name' => '极简企业蓝',
            'desc' => '经典 EasyUI 蓝顶白侧栏的现代扁平升级版',
            'header' => '#1e40af',
            'side' => '#f8fafc',
            'primary' => '#2563eb',
            'canvas' => '#f1f5f9',
            'dark_side' => false,
        ],
        'slate' => [
            'name' => '深炭商务 Pro',
            'desc' => '深炭黑侧栏搭配浅灰工作区，主次分明',
            'header' => '#ffffff',
            'side' => '#0f172a',
            'primary' => '#2563eb',
            'canvas' => '#f1f5f9',
            'dark_side' => true,
        ],
        'fluent' => [
            'name' => '现代桌面 Fluent',
            'desc' => '纯白轻盈侧栏与靛蓝点缀，精致桌面 ERP 质感',
            'header' => '#ffffff',
            'side' => '#ffffff',
            'primary' => '#4f46e5',
            'canvas' => '#f3f4f6',
            'dark_side' => false,
        ],
        'emerald' => [
            'name' => '森林翠绿',
            'desc' => '深墨绿侧栏搭配清爽翠绿，沉稳自然有生机',
            'header' => '#ffffff',
            'side' => '#064e3b',
            'primary' => '#059669',
            'canvas' => '#f1f6f4',
            'dark_side' => true,
        ],
        'navy' => [
            'name' => '深海科技蓝',
            'desc' => '深海蓝侧栏与湖蓝高亮，大型 ERP/金融风',
            'header' => '#ffffff',
            'side' => '#0c233c',
            'primary' => '#0284c7',
            'canvas' => '#f1f5f9',
            'dark_side' => true,
        ],
        'amber' => [
            'name' => '暖阳琥珀',
            'desc' => '柔和暖白护眼画布与琥珀橙，温暖不刺眼',
            'header' => '#ffffff',
            'side' => '#faf7f2',
            'primary' => '#d97706',
            'canvas' => '#f7f4ef',
            'dark_side' => false,
        ],
        'rose' => [
            'name' => '绯红玫瑰',
            'desc' => '深梅灰侧栏搭配玫瑰红主色，品牌电商运营风',
            'header' => '#ffffff',
            'side' => '#1f1622',
            'primary' => '#e11d48',
            'canvas' => '#f6f4f6',
            'dark_side' => true,
        ],
        'violet' => [
            'name' => '极光紫韵',
            'desc' => '深靛紫顶栏配浅紫灰侧栏，现代 SaaS 质感',
            'header' => '#4c1d95',
            'side' => '#faf8ff',
            'primary' => '#7c3aed',
            'canvas' => '#f5f3fa',
            'dark_side' => false,
        ],
        'graphite' => [
            'name' => '极简石墨黑',
            'desc' => '极致克制的黑白灰高定配色，专注数据本身',
            'header' => '#ffffff',
            'side' => '#18181b',
            'primary' => '#18181b',
            'canvas' => '#f4f4f5',
            'dark_side' => true,
        ],
    ];

    public function index(): View
    {
        $smtp = SystemSetting::getGroup('smtp');
        $appearance = SystemSetting::getGroup('appearance');

        return view('admin.settings.index', [
            'themes' => self::THEMES,
            'currentTheme' => $appearance['admin_theme'] ?? 'sage',
            'smtp' => array_merge([
                'smtp_host' => '',
                'smtp_port' => '465',
                'smtp_encryption' => 'ssl',
                'smtp_username' => '',
                'smtp_password' => '',
                'smtp_from_address' => '',
                'smtp_from_name' => config('app.name', 'ShopMate Admin'),
            ], $smtp),
        ]);
    }

    public function saveTheme(Request $request): JsonResponse
    {
        $data = $request->validate([
            'admin_theme' => ['required', Rule::in(array_keys(self::THEMES))],
        ], [
            'admin_theme.in' => '所选主题配色无效',
        ]);

        SystemSetting::setGroup('appearance', [
            'admin_theme' => $data['admin_theme'],
        ], [
            'admin_theme' => '后台主题配色',
        ]);

        $themeName = self::THEMES[$data['admin_theme']]['name'] ?? $data['admin_theme'];

        return response()->json([
            'code' => 0,
            'message' => "已切换并保存主题：{$themeName}",
            'theme' => $data['admin_theme'],
        ]);
    }

    public function saveSmtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'smtp_host' => ['required', 'string', 'max:150'],
            'smtp_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'smtp_encryption' => ['required', 'in:ssl,tls,none'],
            'smtp_username' => ['required', 'string', 'max:150'],
            'smtp_password' => ['nullable', 'string', 'max:200'],
            'smtp_from_address' => ['required', 'email', 'max:150'],
            'smtp_from_name' => ['required', 'string', 'max:100'],
        ], [
            'smtp_host.required' => '请输入 SMTP 服务器地址',
            'smtp_port.required' => '请输入 SMTP 端口',
            'smtp_username.required' => '请输入 SMTP 登录账号',
            'smtp_from_address.required' => '请输入发件人邮箱',
            'smtp_from_address.email' => '发件人邮箱格式不正确',
            'smtp_from_name.required' => '请输入发件人名称',
        ]);

        $existing = SystemSetting::getGroup('smtp');
        if (empty($data['smtp_password']) && ! empty($existing['smtp_password'])) {
            $data['smtp_password'] = $existing['smtp_password'];
        }

        SystemSetting::setGroup('smtp', $data, [
            'smtp_host' => 'SMTP 服务器地址',
            'smtp_port' => 'SMTP 端口',
            'smtp_encryption' => '加密协议',
            'smtp_username' => 'SMTP 账号',
            'smtp_password' => 'SMTP 密码/授权码',
            'smtp_from_address' => '发件人邮箱',
            'smtp_from_name' => '发件人名称',
        ]);

        return response()->json(['code' => 0, 'message' => 'SMTP 设置已保存']);
    }

    public function testMailModal(): View
    {
        return view('admin.settings.test_mail', [
            'defaultEmail' => auth()->user()?->email ?? '',
        ]);
    }

    public function sendTestMail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to_email' => ['required', 'email', 'max:150'],
            'subject' => ['nullable', 'string', 'max:150'],
        ], [
            'to_email.required' => '请输入测试收件人邮箱',
            'to_email.email' => '收件人邮箱格式不正确',
        ]);

        $smtp = SystemSetting::getGroup('smtp');
        if (empty($smtp['smtp_host']) || empty($smtp['smtp_username']) || empty($smtp['smtp_password'])) {
            return response()->json(['code' => 1, 'message' => '请先完整保存 SMTP 服务器地址、账号与密码/授权码'], 422);
        }

        $encryption = $smtp['smtp_encryption'] ?? 'ssl';
        $scheme = match ($encryption) {
            'ssl' => 'smtps',
            'tls' => 'smtp',
            default => 'smtp',
        };

        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'scheme' => $scheme,
            'host' => $smtp['smtp_host'],
            'port' => (int) ($smtp['smtp_port'] ?? 465),
            'username' => $smtp['smtp_username'],
            'password' => $smtp['smtp_password'],
            'timeout' => 15,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);
        Config::set('mail.from', [
            'address' => $smtp['smtp_from_address'] ?: $smtp['smtp_username'],
            'name' => $smtp['smtp_from_name'] ?: config('app.name', 'ShopMate'),
        ]);

        // 清除已缓存的 mailer 实例，确保使用最新动态配置
        Mail::purge('smtp');

        $subject = trim((string) ($data['subject'] ?? '')) ?: 'ShopMate SMTP 测试邮件';
        $body = "您好！\n\n这是一封来自 ShopMate Admin (Laravel 12) 的 SMTP 测试邮件。\n"
            . '发送时间：' . now()->format('Y-m-d H:i:s') . "\n"
            . 'SMTP 主机：' . $smtp['smtp_host'] . ':' . ($smtp['smtp_port'] ?? 465) . "\n\n"
            . '如果您收到此邮件，说明系统邮件服务已配置成功！';

        try {
            Mail::mailer('smtp')->raw($body, function ($message) use ($data, $subject) {
                $message->to($data['to_email'])->subject($subject);
            });

            return response()->json(['code' => 0, 'message' => '测试邮件已成功发送至 ' . $data['to_email']]);
        } catch (Throwable $e) {
            return response()->json([
                'code' => 1,
                'message' => '发送失败：' . $e->getMessage(),
            ], 500);
        }
    }
}
