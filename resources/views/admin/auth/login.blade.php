@extends('admin.layouts.page')

@section('title', '管理员登录 - ShopMate Admin')

@push('styles')
<style>
  body {
    min-height: 100vh;
    background: linear-gradient(135deg, var(--sage-canvas) 0%, var(--sage-primary-soft) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }
  .login-card {
    width: 400px;
    max-width: 100%;
    background: #ffffff;
    border: 1px solid var(--sage-border);
    border-radius: 12px;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.1);
    overflow: hidden;
  }
  .login-head {
    padding: 28px 28px 18px;
    text-align: center;
  }
  .login-logo {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: var(--sage-primary);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 800;
    margin-bottom: 12px;
    box-shadow: 0 6px 16px var(--sage-primary-ring);
  }
  .login-title { font-size: 19px; font-weight: 700; color: var(--sage-text); }
  .login-sub { font-size: 12.5px; color: var(--sage-text-muted); margin-top: 6px; }
  .login-body { padding: 8px 28px 28px; }
  .login-body .layui-input { height: 38px; line-height: 38px; padding-left: 36px; }
  .input-wrap { position: relative; }
  .input-wrap .layui-icon {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--sage-text-muted);
    font-size: 15px;
  }
  .login-submit {
    width: 100%;
    height: 38px;
    line-height: 36px;
    font-size: 14px;
    font-weight: 600;
    margin-top: 8px;
  }
  .login-foot {
    padding: 12px 28px;
    background: var(--sage-table-foot);
    border-top: 1px solid var(--sage-border-light);
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: var(--sage-text-muted);
  }
  .login-foot a { color: var(--sage-primary); text-decoration: none; }
</style>
@endpush

@section('content')
<div class="login-card">
  <div class="login-head">
    <div class="login-logo">SM</div>
    <div class="login-title">ShopMate 后台管理</div>
    <div class="login-sub">支持使用用户名或电子邮箱登录</div>
  </div>

  <div class="login-body">
    <form class="layui-form" lay-filter="loginForm">
      <div class="sage-field">
        <label class="sage-field-label">用户名 / 邮箱</label>
        <div class="input-wrap">
          <i class="layui-icon layui-icon-username"></i>
          <input type="text" name="account" lay-verify="required" placeholder="请输入用户名或邮箱（如 admin）" autocomplete="username" class="layui-input" value="{{ old('account') }}" />
        </div>
      </div>

      <div class="sage-field">
        <label class="sage-field-label">登录密码</label>
        <div class="input-wrap">
          <i class="layui-icon layui-icon-password"></i>
          <input type="password" name="password" lay-verify="required" placeholder="请输入登录密码" autocomplete="current-password" class="layui-input" />
        </div>
      </div>

      <div class="sage-field" style="display:flex;align-items:center;justify-content:space-between;">
        <input type="checkbox" name="remember" value="1" lay-skin="primary" title="记住登录状态" checked />
      </div>

      <button type="submit" lay-submit lay-filter="doLogin" id="loginBtn" class="layui-btn login-submit">立即登录</button>
    </form>
  </div>

  <div class="login-foot">
    <span>Laravel 12 + Layui</span>
    <a href="{{ url('/') }}">返回前台主页</a>
  </div>
</div>
@endsection

@push('scripts')
<script>
layui.use(['form'], function () {
  var form = layui.form;

  form.on('submit(doLogin)', function (data) {
    var btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.textContent = '登录中...';

    AdminUI.request('{{ route("admin.login.submit", [], false) }}', {
      method: 'POST',
      data: data.field
    }).then(function (res) {
      AdminUI.toast('登录成功，正在进入系统...', 'success');
      setTimeout(function () {
        window.location.href = res.redirect || '{{ route("admin.index", [], false) }}';
      }, 400);
    }).catch(function (err) {
      AdminUI.toast(err.message || '登录失败', 'error');
      btn.disabled = false;
      btn.textContent = '立即登录';
    });

    return false;
  });
});
</script>
@endpush
