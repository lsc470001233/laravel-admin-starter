@extends('admin.layouts.page')

@section('title', '用户管理')

@section('content')
<div class="sage-page">
  <!-- 紧凑搜索栏 -->
  <form class="sage-card sage-filter-card layui-form" lay-filter="userFilter">
    <input type="text" name="keyword" id="keyword" placeholder="搜索用户名 / 姓名 / 邮箱" class="layui-input" style="width: 220px;" />
    <div style="width: 130px;">
      <select name="status" id="statusFilter">
        <option value="">全部状态</option>
        <option value="1">已启用</option>
        <option value="0">已禁用</option>
      </select>
    </div>
    <button type="submit" lay-submit lay-filter="searchUsers" class="layui-btn">
      <i class="layui-icon layui-icon-search"></i> 查询
    </button>
    <button type="button" id="resetBtn" class="layui-btn layui-btn-primary">重置</button>
  </form>

  <!-- 高密度表格卡片 -->
  <div class="sage-card sage-table-card">
    <div class="sage-toolbar">
      <div class="sage-toolbar-left">
        <button type="button" id="addUserBtn" class="layui-btn">
          <i class="layui-icon layui-icon-add-1"></i> 新增用户
        </button>
        <button type="button" id="refreshBtn" class="layui-btn layui-btn-primary">
          <i class="layui-icon layui-icon-refresh-3"></i> 刷新
        </button>
      </div>
      <div class="sage-toolbar-right" style="font-size: 12px; color: var(--sage-text-muted);">
        交互模式：新增/编辑使用右侧抽屉 · 改密使用居中弹窗
      </div>
    </div>

    <div style="flex: 1; min-height: 0;">
      <table id="userTable" lay-filter="userTable"></table>
    </div>
  </div>
</div>

<script type="text/html" id="userRowOps">
  <a class="sage-op" lay-event="edit">编辑</a>
  <span class="sage-op-sep">|</span>
  <a class="sage-op" lay-event="password">改密</a>
  <span class="sage-op-sep">|</span>
  <a class="sage-op sage-op-danger" lay-event="del">删除</a>
</script>
@endsection

@push('scripts')
<script>
layui.use(['table', 'form'], function () {
  var table = layui.table;
  var form = layui.form;

  var tableIns = table.render({
    elem: '#userTable',
    url: '{{ route("admin.users.list", [], false) }}',
    page: true,
    limit: 20,
    limits: [10, 20, 50, 100],
    height: 'full-142',
    cols: [[
      { field: 'id', title: 'ID', width: 70, align: 'center' },
      { field: 'username', title: '用户名 (唯一)', width: 140, templet: function (d) {
        return '<strong style="color:var(--sage-primary);">' + layui.util.escape(d.username) + '</strong>';
      }},
      { field: 'name', title: '显示姓名', width: 130 },
      { field: 'email', title: '电子邮箱', minWidth: 180 },
      { field: 'is_admin', title: '角色', width: 100, align: 'center', templet: function (d) {
        return d.is_admin
          ? AdminUI.badgeHtml('管理员', 'primary')
          : AdminUI.badgeHtml('普通用户', 'gray');
      }},
      { field: 'status', title: '状态', width: 96, align: 'center', templet: function (d) {
        var checked = d.status === 1 ? 'checked' : '';
        var disabled = d.username === 'admin' ? 'disabled' : '';
        return '<input type="checkbox" lay-skin="switch" lay-text="启用|禁用" lay-filter="toggleUserStatus" value="' + d.id + '" ' + checked + ' ' + disabled + '>';
      }},
      { field: 'last_login_at', title: '最后登录时间', width: 160 },
      { field: 'last_login_ip', title: '登录 IP', width: 120 },
      { field: 'created_at', title: '创建时间', width: 145 },
      { title: '操作', width: 150, fixed: 'right', toolbar: '#userRowOps' }
    ]]
  });

  window.reloadUserTable = function () {
    tableIns.reload();
  };

  form.on('submit(searchUsers)', function (data) {
    tableIns.reload({
      where: { keyword: data.field.keyword, status: data.field.status },
      page: { curr: 1 }
    });
    return false;
  });

  document.getElementById('resetBtn').addEventListener('click', function () {
    document.getElementById('keyword').value = '';
    document.getElementById('statusFilter').value = '';
    form.render('select');
    tableIns.reload({ where: { keyword: '', status: '' }, page: { curr: 1 } });
  });

  document.getElementById('refreshBtn').addEventListener('click', function () {
    tableIns.reload();
  });

  // 新增用户：使用右侧抽屉
  document.getElementById('addUserBtn').addEventListener('click', function () {
    AdminUI.openDrawer({
      title: '新增用户',
      url: '{{ route("admin.users.create", [], false) }}',
      width: '460px'
    });
  });

  // 快捷状态开关
  form.on('switch(toggleUserStatus)', function (obj) {
    var id = obj.value;
    AdminUI.request('/admin/users/' + id + '/status', { method: 'PATCH' })
      .then(function (res) {
        AdminUI.toast(res.message, 'success');
      })
      .catch(function (err) {
        AdminUI.toast(err.message, 'error');
        tableIns.reload();
      });
  });

  // 行操作
  table.on('tool(userTable)', function (obj) {
    var d = obj.data;
    if (obj.event === 'edit') {
      AdminUI.openDrawer({
        title: '编辑用户：' + d.username,
        url: '/admin/users/' + d.id + '/edit',
        width: '460px'
      });
    } else if (obj.event === 'password') {
      AdminUI.openModal({
        title: '重置密码：' + d.username,
        url: '/admin/users/' + d.id + '/password',
        width: '440px',
        height: '320px'
      });
    } else if (obj.event === 'del') {
      AdminUI.confirm('确认永久删除用户 <strong>' + layui.util.escape(d.username) + '</strong> 吗？', function () {
        AdminUI.request('/admin/users/' + d.id, { method: 'DELETE' })
          .then(function (res) {
            AdminUI.toast(res.message, 'success');
            tableIns.reload();
          })
          .catch(function (err) {
            AdminUI.toast(err.message, 'error');
          });
      });
    }
  });
});
</script>
@endpush
