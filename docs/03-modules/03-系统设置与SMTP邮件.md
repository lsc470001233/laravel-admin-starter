# 03 - 系统设置与 SMTP 邮件

## 模块设计（`Admin\SettingController`）
- 配置页面：`GET /admin/settings`
- 保存接口：`POST /admin/settings/smtp`
- 测试邮件弹窗：`GET /admin/settings/smtp/test-modal`
- 发送测试邮件：`POST /admin/settings/smtp/test`

## 核心逻辑
1. **持久化存储**：所有 SMTP 参数以 `group = 'smtp'` 存入 `system_settings` 表，并通过 `SystemSetting::getGroup('smtp')` 做缓存读取与更新失效。
2. **密码保护**：编辑保存时若 `smtp_password` 留空，自动保留数据库中原有密码/授权码不被清空。
3. **动态发信**：发送测试邮件时在运行时覆盖 `mail.mailers.smtp`（支持 `ssl` -> `smtps`、`tls`/`none` -> `smtp`）并调用 `Mail::purge('smtp')` 刷新 Mailer 实例，实时返回成功消息或完整异常原因。
