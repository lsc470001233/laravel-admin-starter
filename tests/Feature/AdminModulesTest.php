<?php

namespace Tests\Feature;

use App\Models\DictType;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminModulesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_home_page_is_accessible(): void
    {
        $this->get('/')->assertOk()->assertSee('ShopMate');
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_login_with_username_and_email(): void
    {
        // 用户名登录
        $this->postJson(route('admin.login.submit'), [
            'account' => 'admin',
            'password' => 'admin123456',
        ])->assertOk()->assertJsonPath('code', 0);

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));

        // 邮箱登录
        $this->postJson(route('admin.login.submit'), [
            'account' => 'admin@admin.com',
            'password' => 'admin123456',
        ])->assertOk()->assertJsonPath('code', 0);
    }

    public function test_user_management_crud_and_unique_username(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $this->actingAs($admin);

        $this->get(route('admin.users.index'))->assertOk();
        $this->getJson(route('admin.users.list'))->assertOk()->assertJsonPath('code', 0);

        // 创建新用户
        $this->postJson(route('admin.users.store'), [
            'username' => 'tester01',
            'name' => '测试员',
            'email' => 'tester01@example.com',
            'password' => 'secret123',
            'status' => 1,
            'is_admin' => true,
        ])->assertOk()->assertJsonPath('code', 0);

        // 重复用户名应被拒绝
        $this->postJson(route('admin.users.store'), [
            'username' => 'tester01',
            'name' => '重复测试',
            'email' => 'tester02@example.com',
            'password' => 'secret123',
            'status' => 1,
        ])->assertStatus(422);

        $created = User::query()->where('username', 'tester01')->firstOrFail();

        // 重置密码
        $this->putJson(route('admin.users.password_update', $created), [
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertOk()->assertJsonPath('code', 0);

        // 默认 admin 不能删除
        $this->deleteJson(route('admin.users.destroy', $admin))->assertStatus(422);

        // 删除测试用户
        $this->deleteJson(route('admin.users.destroy', $created))->assertOk();
    }

    public function test_dict_master_detail_crud(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $this->actingAs($admin);

        $this->get(route('admin.dicts.index'))->assertOk();

        // 创建主表字典类型
        $this->postJson(route('admin.dicts.types.store'), [
            'name' => '测试字典',
            'code' => 'test_dict_code',
            'status' => 1,
            'remark' => '自动化测试',
        ])->assertOk()->assertJsonPath('code', 0);

        $type = DictType::query()->where('code', 'test_dict_code')->firstOrFail();

        // 创建从表字典项
        $this->postJson(route('admin.dicts.items.store', $type), [
            'label' => '选项A',
            'value' => 'opt_a',
            'color' => 'teal',
            'sort' => 10,
            'status' => 1,
        ])->assertOk()->assertJsonPath('code', 0);

        // 从表列表联动查询
        $this->getJson(route('admin.dicts.items.list', $type))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('data.0.value', 'opt_a');

        // 同类型下重复 value 拒绝
        $this->postJson(route('admin.dicts.items.store', $type), [
            'label' => '选项A重复',
            'value' => 'opt_a',
            'color' => 'blue',
            'sort' => 20,
            'status' => 1,
        ])->assertStatus(422);
    }

    public function test_smtp_settings_save_and_test_mail(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $this->actingAs($admin);

        $this->get(route('admin.settings.index'))->assertOk();

        $this->postJson(route('admin.settings.smtp.save'), [
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 465,
            'smtp_encryption' => 'ssl',
            'smtp_username' => 'noreply@example.com',
            'smtp_password' => 'auth-secret-code',
            'smtp_from_address' => 'noreply@example.com',
            'smtp_from_name' => 'ShopMate 测试',
        ])->assertOk()->assertJsonPath('code', 0);

        // 再次保存且密码留空，应保留原密码
        $this->postJson(route('admin.settings.smtp.save'), [
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 465,
            'smtp_encryption' => 'ssl',
            'smtp_username' => 'noreply@example.com',
            'smtp_password' => '',
            'smtp_from_address' => 'noreply@example.com',
            'smtp_from_name' => 'ShopMate 测试2',
        ])->assertOk();

        $saved = SystemSetting::getGroup('smtp');
        $this->assertSame('auth-secret-code', $saved['smtp_password']);
        $this->assertSame('ShopMate 测试2', $saved['smtp_from_name']);

        // 使用 Mail::fake 验证发送测试邮件接口
        Mail::fake();
        $this->postJson(route('admin.settings.smtp.test'), [
            'to_email' => 'admin@admin.com',
            'subject' => '测试主题',
        ])->assertOk()->assertJsonPath('code', 0);

        // 验证多主题配色保存与视图注入
        $this->postJson(route('admin.settings.theme.save'), [
            'admin_theme' => 'slate',
        ])->assertOk()->assertJsonPath('theme', 'slate');

        $this->get(route('admin.index'))
            ->assertOk()
            ->assertSee('data-theme="slate"', false);
    }
}
