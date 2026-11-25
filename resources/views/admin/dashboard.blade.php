@extends('admin.layouts.page')

@section('title', '工作台')

@push('styles')
<style>
  .dash-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
  }
  .stat-card {
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .stat-label { font-size: 12.5px; color: var(--sage-text-muted); }
  .stat-val { font-size: 24px; font-weight: 700; color: var(--sage-text); line-height: 1.2; }
  .stat-sub { font-size: 12px; color: var(--sage-text-muted); }
  .dash-row {
    display: grid;
    grid-template-columns: 1.3fr 1fr;
    gap: 12px;
  }
  .panel-head {
    padding: 11px 16px;
    border-bottom: 1px solid var(--sage-border-light);
    font-weight: 600;
    font-size: 13.5px;
    color: var(--sage-text);
  }
  .panel-body { padding: 14px 16px; }
  .quick-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
  }
  .quick-item {
    padding: 14px;
    border: 1px solid var(--sage-border);
    border-radius: 6px;
    background: var(--sage-table-foot);
    cursor: pointer;
    transition: all 0.15s;
  }
  .quick-item:hover {
    border-color: var(--sage-primary);
    background: var(--sage-primary-soft);
  }
  .quick-item .title { font-weight: 600; color: var(--sage-text); margin-bottom: 4px; }
  .quick-item .desc { font-size: 12px; color: var(--sage-text-muted); }
  .env-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
  .env-table td { padding: 7px 10px; border-bottom: 1px solid var(--sage-border-light); }
  .env-table td:first-child { color: var(--sage-text-muted); width: 120px; }
  @media (max-width: 900px) {
    .dash-grid { grid-template-columns: repeat(2, 1fr); }
    .dash-row { grid-template-columns: 1fr; }
  }
</style>
@endpush

@section('content')
<div class="sage-page">
  <div class="dash-grid">
    <div class="sage-card stat-card">
      <div class="stat-label">系统用户总数</div>
      <div class="stat-val">{{ $stats['users_total'] }}</div>
      <div class="stat-sub">当前启用：{{ $stats['users_active'] }} 人</div>
    </div>
    <div class="sage-card stat-card">
      <div class="stat-label">数据字典类型</div>
      <div class="stat-val">{{ $stats['dict_types'] }}</div>
      <div class="stat-sub">字典明细项：{{ $stats['dict_items'] }} 条</div>
    </div>
    <div class="sage-card stat-card">
      <div class="stat-label">SMTP 邮件服务</div>
      <div class="stat-val" style="font-size:18px;margin-top:4px;">
        @if($stats['smtp_configured'])
          <span class="sage-badge sage-badge-green">已配置</span>
        @else
          <span class="sage-badge sage-badge-amber">待配置</span>
        @endif
      </div>
      <div class="stat-sub">可在系统设置中配置并测试发信</div>
    </div>
    <div class="sage-card stat-card">
      <div class="stat-label">当前登录账号</div>
      <div class="stat-val" style="font-size:18px;margin-top:4px;">{{ auth()->user()->username }}</div>
      <div class="stat-sub">{{ auth()->user()->email }}</div>
    </div>
  </div>

  <div class="dash-row">
    <div class="sage-card">
      <div class="panel-head">快捷功能入口</div>
      <div class="panel-body">
        <div class="quick-grid">
          <div class="quick-item" onclick="openModule('users', '用户管理', '{{ route('admin.users.index', [], false) }}')">
            <div class="title">用户管理</div>
            <div class="desc">右侧抽屉编辑 · 弹窗改密</div>
          </div>
          <div class="quick-item" onclick="openModule('dicts', '数据字典', '{{ route('admin.dicts.index', [], false) }}')">
            <div class="title">数据字典</div>
            <div class="desc">上下主从分屏实时联动</div>
          </div>
          <div class="quick-item" onclick="openModule('settings', '系统设置', '{{ route('admin.settings.index', [], false) }}')">
            <div class="title">系统设置</div>
            <div class="desc">SMTP 存储与测试邮件发送</div>
          </div>
        </div>
      </div>
    </div>

    <div class="sage-card">
      <div class="panel-head">运行环境概览</div>
      <div class="panel-body" style="padding: 8px 16px;">
        <table class="env-table">
          <tr><td>框架版本</td><td>Laravel {{ $env['laravel'] }}</td></tr>
          <tr><td>PHP 版本</td><td>PHP {{ $env['php'] }}</td></tr>
          <tr><td>数据库名称</td><td>{{ $env['db'] }}</td></tr>
          <tr><td>前端组件库</td><td>Layui 2.13 (护眼青灰现代重设计)</td></tr>
          <tr><td>服务器时间</td><td>{{ $env['server_time'] }}</td></tr>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openModule(id, title, url) {
  if (window.parent && window.parent.AdminTabs) {
    window.parent.AdminTabs.open(id, title, url, true);
  } else {
    window.location.href = url;
  }
}
</script>
@endpush
