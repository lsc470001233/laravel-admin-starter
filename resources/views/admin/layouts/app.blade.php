<!DOCTYPE html>
<html lang="zh-CN" data-theme="{{ $adminTheme ?? 'sage' }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ config('app.name', 'ShopMate Admin') }} - 后台管理系统</title>
  <link rel="stylesheet" href="/assets/layui/css/layui.css" />
  <link rel="stylesheet" href="/assets/admin/css/layui-sage-modern.css?v={{ filemtime(public_path('assets/admin/css/layui-sage-modern.css')) }}" />
</head>
<body>
<div class="sage-shell">
  <!-- 顶部栏 -->
  <header class="sage-header">
    <div class="sage-header-left">
      <a href="{{ route('admin.index', [], false) }}" class="sage-brand">
        <span class="sage-brand-icon">SM</span>
        <span>ShopMate</span>
        <span class="sage-brand-tag">Laravel 12</span>
      </a>

      <button type="button" id="toggleSideBtn" class="sage-icon-btn" title="折叠 / 展开菜单">
        <i class="layui-icon layui-icon-shrink-right"></i>
      </button>

      <div class="sage-crumb">
        <span>后台管理</span>
        <span>/</span>
        <strong id="crumbCurrent">工作台</strong>
      </div>
    </div>

    <div class="sage-header-right">
      <a href="{{ url('/') }}" target="_blank" class="sage-icon-btn" title="访问前台主页">
        <i class="layui-icon layui-icon-website"></i>
      </a>
      <button type="button" id="refreshTabBtn" class="sage-icon-btn" title="刷新当前标签页">
        <i class="layui-icon layui-icon-refresh-3"></i>
      </button>

      <div id="userMenuBtn" class="sage-user-trigger">
        <span class="sage-avatar">{{ mb_substr(auth()->user()->name ?: auth()->user()->username, 0, 1) }}</span>
        <span>{{ auth()->user()->name }} ({{ auth()->user()->username }})</span>
        <i class="layui-icon layui-icon-down" style="font-size: 11px;"></i>
      </div>
    </div>
  </header>

  <!-- 主体 -->
  <div class="sage-body">
    <!-- 左侧精简导航栏 -->
    <aside id="sidebar" class="sage-side">
      <div class="sage-side-group">总览</div>
      <div class="sage-nav-item active" data-id="dashboard" data-title="工作台" data-url="{{ route('admin.dashboard', [], false) }}">
        <i class="layui-icon layui-icon-home"></i>
        <span class="sage-nav-text">工作台</span>
      </div>

      <div class="sage-side-group">系统管理</div>
      <div class="sage-nav-item" data-id="users" data-title="用户管理" data-url="{{ route('admin.users.index', [], false) }}">
        <i class="layui-icon layui-icon-user"></i>
        <span class="sage-nav-text">用户管理</span>
      </div>
      <div class="sage-nav-item" data-id="dicts" data-title="数据字典" data-url="{{ route('admin.dicts.index', [], false) }}">
        <i class="layui-icon layui-icon-template-1"></i>
        <span class="sage-nav-text">数据字典</span>
      </div>
      <div class="sage-nav-item" data-id="settings" data-title="系统设置" data-url="{{ route('admin.settings.index', [], false) }}">
        <i class="layui-icon layui-icon-set"></i>
        <span class="sage-nav-text">系统设置</span>
      </div>
    </aside>

    <!-- 右侧多标签工作区 -->
    <main class="sage-main">
      <div class="sage-tab-bar">
        <div id="tabBar" class="sage-tabs"></div>
        <div class="sage-tab-actions">
          <button type="button" id="closeOtherTabsBtn" class="layui-btn layui-btn-primary layui-btn-xs">关闭其他</button>
        </div>
      </div>
      <div id="iframeWrap" class="sage-iframe-wrap"></div>
    </main>
  </div>
</div>

<form id="logoutForm" method="POST" action="{{ route('admin.logout', [], false) }}" style="display:none;">
  @csrf
</form>

<script src="/assets/layui/layui.js"></script>
<script src="/assets/admin/js/admin-common.js?v={{ filemtime(public_path('assets/admin/js/admin-common.js')) }}"></script>
<script>
layui.use(['dropdown', 'layer'], function () {
  var dropdown = layui.dropdown;
  var tabs = [];
  var activeId = null;

  var tabBar = document.getElementById('tabBar');
  var iframeWrap = document.getElementById('iframeWrap');
  var crumbCurrent = document.getElementById('crumbCurrent');

  function openTab(id, title, url, closable) {
    if (closable === undefined) closable = true;
    var existing = tabs.find(function (t) { return t.id === id; });
    if (!existing) {
      tabs.push({ id: id, title: title, url: url, closable: closable });
      var iframe = document.createElement('iframe');
      iframe.className = 'sage-iframe';
      iframe.setAttribute('data-id', id);
      iframe.src = url;
      iframeWrap.appendChild(iframe);
    }
    switchTab(id);
  }

  function switchTab(id) {
    activeId = id;
    renderTabs();
    document.querySelectorAll('.sage-iframe').forEach(function (el) {
      el.classList.toggle('active', el.getAttribute('data-id') === id);
    });
    document.querySelectorAll('.sage-nav-item').forEach(function (el) {
      el.classList.toggle('active', el.getAttribute('data-id') === id);
    });
    var cur = tabs.find(function (t) { return t.id === id; });
    if (cur) crumbCurrent.textContent = cur.title;
  }

  function closeTab(id) {
    var idx = tabs.findIndex(function (t) { return t.id === id; });
    if (idx === -1 || !tabs[idx].closable) return;
    tabs.splice(idx, 1);
    var frame = iframeWrap.querySelector('iframe[data-id="' + id + '"]');
    if (frame) frame.remove();
    if (activeId === id) {
      var next = tabs[Math.max(0, idx - 1)];
      if (next) switchTab(next.id);
    } else {
      renderTabs();
    }
  }

  function renderTabs() {
    tabBar.innerHTML = tabs.map(function (t) {
      var closeHtml = t.closable ? '<span class="sage-tab-close" data-close="' + t.id + '">&times;</span>' : '';
      return '<div class="sage-tab ' + (t.id === activeId ? 'active' : '') + '" data-tab="' + t.id + '">' +
        '<span>' + t.title + '</span>' + closeHtml +
      '</div>';
    }).join('');
  }

  // 暴露给子页面按需打开新标签 & 全局实时切换主题
  window.AdminTabs = { open: openTab, switch: switchTab };
  window.AdminTheme = {
    apply: function (theme) {
      document.documentElement.setAttribute('data-theme', theme);
      document.querySelectorAll('.sage-iframe').forEach(function (frame) {
        try {
          if (frame.contentDocument && frame.contentDocument.documentElement) {
            frame.contentDocument.documentElement.setAttribute('data-theme', theme);
          }
        } catch (e) {}
      });
    }
  };

  tabBar.addEventListener('click', function (e) {
    var closeId = e.target.getAttribute('data-close');
    if (closeId) {
      e.stopPropagation();
      closeTab(closeId);
      return;
    }
    var tabEl = e.target.closest('.sage-tab');
    if (tabEl) switchTab(tabEl.getAttribute('data-tab'));
  });

  document.querySelectorAll('.sage-nav-item').forEach(function (el) {
    el.addEventListener('click', function () {
      openTab(
        el.getAttribute('data-id'),
        el.getAttribute('data-title'),
        el.getAttribute('data-url'),
        el.getAttribute('data-id') !== 'dashboard'
      );
    });
  });

  document.getElementById('toggleSideBtn').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('collapsed');
  });

  document.getElementById('refreshTabBtn').addEventListener('click', function () {
    var frame = iframeWrap.querySelector('iframe.active');
    if (frame) frame.contentWindow.location.reload();
  });

  document.getElementById('closeOtherTabsBtn').addEventListener('click', function () {
    tabs.slice().forEach(function (t) {
      if (t.closable && t.id !== activeId) closeTab(t.id);
    });
  });

  dropdown.render({
    elem: '#userMenuBtn',
    data: [
      { title: '个人资料 / 改密', id: 'password' },
      { type: '-' },
      { title: '安全退出登录', id: 'logout' }
    ],
    click: function (obj) {
      if (obj.id === 'logout') {
        AdminUI.confirm('确认退出后台管理系统吗？', function () {
          document.getElementById('logoutForm').submit();
        });
      } else if (obj.id === 'password') {
        AdminUI.openModal({
          title: '修改我的密码',
          url: '{{ route("admin.users.password_form", auth()->id(), false) }}',
          width: '440px',
          height: '320px'
        });
      }
    }
  });

  // 默认打开工作台
  openTab('dashboard', '工作台', '{{ route("admin.dashboard", [], false) }}', false);
});
</script>
</body>
</html>
