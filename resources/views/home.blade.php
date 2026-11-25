<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ShopMate — 首页</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Microsoft YaHei", sans-serif;
      background: #eff4f3;
      color: #1e293b;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .nav {
      height: 60px;
      background: #ffffff;
      border-bottom: 1px solid #d9e4e2;
      padding: 0 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 17px;
      font-weight: 700;
      color: #134e4a;
      text-decoration: none;
    }
    .logo {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background: #0f766e;
      color: #fff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 800;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      height: 36px;
      padding: 0 18px;
      border-radius: 6px;
      font-size: 13.5px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.15s;
    }
    .btn-primary { background: #0f766e; color: #fff; }
    .btn-primary:hover { background: #115e59; }
    .btn-outline { background: #fff; color: #134e4a; border: 1px solid #cbd5e1; }
    .btn-outline:hover { border-color: #0f766e; color: #0f766e; }

    .hero {
      max-width: 960px;
      margin: 72px auto 0;
      padding: 0 24px;
      text-align: center;
    }
    .pill {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 999px;
      background: #ccfbf1;
      color: #0f766e;
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 18px;
    }
    h1 {
      font-size: 38px;
      line-height: 1.25;
      color: #0f172a;
      margin-bottom: 14px;
    }
    .desc {
      font-size: 15px;
      color: #527975;
      max-width: 620px;
      margin: 0 auto 28px;
      line-height: 1.7;
    }
    .actions { display: flex; justify-content: center; gap: 12px; margin-bottom: 56px; }
    .grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      text-align: left;
    }
    .card {
      background: #ffffff;
      border: 1px solid #d9e4e2;
      border-radius: 8px;
      padding: 20px;
    }
    .card h3 { font-size: 15px; color: #134e4a; margin-bottom: 8px; }
    .card p { font-size: 13px; color: #64748b; line-height: 1.6; }
    .footer {
      margin-top: auto;
      padding: 24px;
      text-align: center;
      font-size: 12px;
      color: #64827e;
    }
    @media (max-width: 720px) { .grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <header class="nav">
    <a href="{{ url('/') }}" class="brand">
      <span class="logo">SM</span>
      <span>ShopMate</span>
    </a>
    <div>
      @auth
        <a href="{{ route('admin.index', [], false) }}" class="btn btn-primary">进入管理后台</a>
      @else
        <a href="{{ route('admin.login', [], false) }}" class="btn btn-primary">管理员登录</a>
      @endauth
    </div>
  </header>

  <main class="hero">
    <span class="pill">Laravel 12 · Layui 2.13 · 护眼青灰企业架构</span>
    <h1>ShopMate 业务与管理平台</h1>
    <p class="desc">
      这是前台展示主页占位页面。后台采用护眼青灰主题与 EasyUI 高密度交互融合设计，支持多标签页、右侧抽屉、居中弹窗与上下主从分屏。
    </p>
    <div class="actions">
      <a href="{{ route('admin.index', [], false) }}" class="btn btn-primary">进入后台管理系统</a>
      <a href="{{ route('admin.login', [], false) }}" class="btn btn-outline">登录入口</a>
    </div>

    <div class="grid">
      <div class="card">
        <h3>用户与认证</h3>
        <p>支持用户名或邮箱双方式登录，用户名全局唯一，右侧抽屉快速编辑与弹窗安全改密。</p>
      </div>
      <div class="card">
        <h3>数据字典主从分屏</h3>
        <p>采用 EasyUI 经典上下主从表联动，点击上方字典类型，下方明细项即时切换维护。</p>
      </div>
      <div class="card">
        <h3>系统设置与 SMTP</h3>
        <p>数据库持久化管理邮件服务器参数，支持弹窗一键发送测试邮件并即时反馈结果。</p>
      </div>
    </div>
  </main>

  <footer class="footer">
    &copy; {{ date('Y') }} ShopMate · Powered by Laravel {{ app()->version() }}
  </footer>
</body>
</html>
