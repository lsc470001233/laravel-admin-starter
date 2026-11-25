# Laravel Admin Starter

基于 **Laravel 12** + **Layui 2.13.9** 构建的通用后台管理基础框架（Starter Kit），内置 **10 套精选后台主题配色**（默认自研护眼青灰 Sage Teal）、多标签页（MDI Tabs）架构与开箱即用的基础业务模块，适合作为各类企业后台与业务系统的开发起点。

> 本仓库已开启 GitHub **Template Repository**，可直接点击右上角 **Use this template** 一键基于此框架创建新项目。

---

## 核心特性

- **现代技术栈，零 CDN 依赖**：后端采用 Laravel 12（PHP 8.2+），前端基于 Layui 2.13.9 本地静态资源（`public/assets/layui/`），离线/内网环境同样开箱即用。
- **10 套可热切换主题配色**：内置 `sage`（护眼青灰，默认）、`blue`（极简企业蓝）、`slate`（深炭商务 Pro）、`fluent`（现代桌面 Fluent）、`emerald`（森林翠绿）、`navy`（深海科技蓝）、`amber`（暖阳琥珀）、`rose`（绯红玫瑰）、`violet`（极光紫韵）、`graphite`（极简石墨黑），支持在「系统设置」中实时预览、数据库持久化保存，并无刷新同步至主框架与所有已打开的 `iframe` 标签页。
- **经典高效后台布局**：左侧可折叠导航栏 + 顶部多标签页（MDI Tabs）+ `iframe` 子页隔离模式。
- **三种统一交互范式（`AdminUI`）**：
  - **上下主从分屏**（`.sage-split-page`）：支持拖拽调节高度，适用于主表/明细表联动（如数据字典、订单明细）。
  - **右侧抽屉编辑**（`AdminUI.openDrawer`）：适用于字段较多的表单新增与编辑（如用户管理）。
  - **居中轻量弹窗**（`AdminUI.openModal`）：适用于聚焦确认的轻量操作（如重置密码、字典项编辑、测试邮件）。
- **开箱即用的基础模块**：
  - **双凭证登录认证**（`/admin/login`）：支持用户名或邮箱登录，校验启用状态与管理员权限，自动记录最后登录时间与 IP。
  - **工作台仪表盘**（`/admin/dashboard`）：系统概览与快捷入口。
  - **用户管理**（`/admin/users`）：分页检索、右侧抽屉新增/编辑、密码重置弹窗、状态切换，内置默认管理员与当前登录账号防误删/防禁用保护。
  - **数据字典**（`/admin/dicts`）：主从分屏管理字典类型与字典项，支持 6 种软色徽章色调与排序。
  - **系统设置与动态 SMTP**（`/admin/settings`）：后台主题切换、SMTP 参数持久化与缓存、密码留空保护、运行时动态切换 Mailer 发送测试邮件。

---

## 环境要求

- PHP `>= 8.2`
- Composer `>= 2.x`
- MySQL 8.0+ / MariaDB（或 SQLite 用于本地快速体验）
- Node.js `>= 18`（可选，仅在使用 Vite 编译自定义前端资源时需要）

---

## 快速开始

### 1. 基于模板创建或克隆项目

在 GitHub 点击 **Use this template** 创建你的新仓库，或直接克隆：

```bash
git clone git@github.com:lsc470001233/laravel-admin-starter.git my-project
cd my-project
```

### 2. 安装依赖与初始化环境

```bash
composer install
cp .env.example .env
php artisan key:generate
```

编辑 `.env` 配置你的数据库连接（MySQL 或 SQLite），例如：

```env
APP_NAME="My Admin"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. 执行数据库迁移与初始数据填充

```bash
php artisan migrate --seed
```

`DatabaseSeeder` 会自动初始化：
- 默认超级管理员账号
- 默认 SMTP 配置项
- 常用示例数据字典（通用状态、用户角色类型、SMTP 加密方式）

### 4. 启动服务与登录后台

```bash
php artisan serve
```

- 前台主页：`http://localhost:8000`
- 后台登录：`http://localhost:8000/admin/login`
- 默认管理员账号：`admin`（或邮箱 `admin@admin.com`）
- 默认密码：`admin123456`（**生产环境请务必第一时间修改**）

---

## 运行测试

```bash
php artisan test
```

---

## 目录结构

```text
├── app/
│   ├── Http/Controllers/
│   │   ├── HomeController.php            # 前台主页控制器
│   │   └── Admin/                        # 后台模块控制器（认证、工作台、用户、字典、系统设置）
│   ├── Models/                           # User / DictType / DictItem / SystemSetting
│   └── Providers/AppServiceProvider.php  # 全局视图主题变量注入
├── database/
│   ├── migrations/                       # 数据库迁移文件
│   └── seeders/DatabaseSeeder.php        # 默认管理员、字典与系统配置填充
├── public/assets/
│   ├── layui/                            # Layui 2.13.9 本地静态资源
│   └── admin/
│       ├── css/layui-sage-modern.css     # 10 套企业后台主题样式覆写
│       └── js/admin-common.js            # AdminUI 抽屉/弹窗/公共交互封装
├── resources/views/
│   ├── home.blade.php                    # 前台主页视图
│   └── admin/                            # 后台主框架母版、子页母版及各业务视图
├── routes/web.php                        # 前后台路由定义
├── tests/Feature/AdminModulesTest.php    # 后台核心模块功能测试
└── docs/                                 # 架构设计、数据库、前端规范与模块文档
```

---

## 开发文档

项目内置了完整的中文开发文档，详见 [`docs/00-README.md`](./docs/00-README.md)：

- **系统架构**：[系统架构与技术栈](./docs/01-architecture/01-系统架构与技术栈.md) · [数据库设计说明](./docs/01-architecture/02-数据库设计说明.md)
- **前端与主题**：[Layui 护眼青灰主题与交互规范](./docs/02-frontend/01-Layui护眼青灰主题与交互规范.md)
- **业务模块**：[认证与用户管理](./docs/03-modules/01-认证与用户管理.md) · [数据字典主从分屏](./docs/03-modules/02-数据字典主从分屏.md) · [系统设置与 SMTP 邮件](./docs/03-modules/03-系统设置与SMTP邮件.md)

---

## License

本项目基于 [MIT License](https://opensource.org/licenses/MIT) 开源。
