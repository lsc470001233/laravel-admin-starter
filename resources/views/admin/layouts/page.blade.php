<!DOCTYPE html>
<html lang="zh-CN" data-theme="{{ $adminTheme ?? 'sage' }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'ShopMate Admin')</title>
  <link rel="stylesheet" href="/assets/layui/css/layui.css" />
  <link rel="stylesheet" href="/assets/admin/css/layui-sage-modern.css?v={{ filemtime(public_path('assets/admin/css/layui-sage-modern.css')) }}" />
  @stack('styles')
</head>
<body class="@yield('body_class')">
  @yield('content')
  <script src="/assets/layui/layui.js"></script>
  <script src="/assets/admin/js/admin-common.js?v={{ filemtime(public_path('assets/admin/js/admin-common.js')) }}"></script>
  @stack('scripts')
</body>
</html>
