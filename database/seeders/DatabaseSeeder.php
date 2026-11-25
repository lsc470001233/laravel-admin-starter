<?php

namespace Database\Seeders;

use App\Models\DictItem;
use App\Models\DictType;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 初始化默认超级管理员
        User::query()->updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => '超级管理员',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin123456'),
                'status' => 1,
                'is_admin' => true,
            ]
        );

        // 2. 初始化默认 SMTP 系统设置
        $smtpDefaults = [
            'smtp_host' => ['', 'SMTP 服务器地址'],
            'smtp_port' => ['465', 'SMTP 端口'],
            'smtp_encryption' => ['ssl', '加密协议（ssl / tls / none）'],
            'smtp_username' => ['', 'SMTP 账号'],
            'smtp_password' => ['', 'SMTP 密码/授权码'],
            'smtp_from_address' => ['admin@admin.com', '发件人邮箱'],
            'smtp_from_name' => ['ShopMate 系统通知', '发件人名称'],
        ];
        foreach ($smtpDefaults as $key => [$val, $label]) {
            SystemSetting::query()->firstOrCreate(
                ['key' => $key],
                ['group' => 'smtp', 'value' => $val, 'label' => $label]
            );
        }

        // 3. 初始化常用数据字典（便于展示上下主从分屏）
        $dicts = [
            [
                'name' => '通用状态',
                'code' => 'common_status',
                'remark' => '系统各模块通用的启用/停用状态',
                'items' => [
                    ['label' => '启用', 'value' => '1', 'color' => 'green', 'sort' => 10, 'remark' => '正常可用'],
                    ['label' => '停用', 'value' => '0', 'color' => 'red', 'sort' => 20, 'remark' => '已禁用'],
                ],
            ],
            [
                'name' => '用户角色类型',
                'code' => 'user_role_type',
                'remark' => '区分后台管理员与普通用户',
                'items' => [
                    ['label' => '管理员', 'value' => '1', 'color' => 'teal', 'sort' => 10, 'remark' => '可登录后台管理系统'],
                    ['label' => '普通用户', 'value' => '0', 'color' => 'gray', 'sort' => 20, 'remark' => '仅前台访问'],
                ],
            ],
            [
                'name' => 'SMTP 加密方式',
                'code' => 'smtp_encryption',
                'remark' => '系统设置中邮件发送可选的加密方式',
                'items' => [
                    ['label' => 'SSL (推荐 465)', 'value' => 'ssl', 'color' => 'teal', 'sort' => 10, 'remark' => 'SMTPS 隐式加密'],
                    ['label' => 'TLS (推荐 587)', 'value' => 'tls', 'color' => 'blue', 'sort' => 20, 'remark' => 'STARTTLS 显式加密'],
                    ['label' => '不加密 (25)', 'value' => 'none', 'color' => 'amber', 'sort' => 30, 'remark' => '仅内网测试使用'],
                ],
            ],
        ];

        foreach ($dicts as $d) {
            $type = DictType::query()->updateOrCreate(
                ['code' => $d['code']],
                ['name' => $d['name'], 'status' => 1, 'remark' => $d['remark']]
            );
            foreach ($d['items'] as $item) {
                DictItem::query()->updateOrCreate(
                    ['dict_type_id' => $type->id, 'value' => $item['value']],
                    [
                        'label' => $item['label'],
                        'color' => $item['color'],
                        'sort' => $item['sort'],
                        'status' => 1,
                        'remark' => $item['remark'],
                    ]
                );
            }
        }
    }
}
