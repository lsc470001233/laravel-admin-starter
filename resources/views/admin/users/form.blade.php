@extends('admin.layouts.page')

@section('title', $user ? '编辑用户' : '新增用户')

@section('content')
<div class="sage-form-box">
  @if($user)
    <div class="sage-form-tip">正在通过右侧抽屉编辑用户 <strong>{{ $user->username }}</strong>，保存后列表自动刷新。</div>
  @else
    <div class="sage-form-tip">用户名与电子邮箱全局唯一，均可作为后台登录凭证。</div>
  @endif

  <form class="layui-form" lay-filter="userForm">
    <div class="sage-field">
      <label class="sage-field-label">用户名（唯一登录名）<span class="req">*</span></label>
      <input type="text" name="username" lay-verify="required" placeholder="字母/数字/下划线，如 zhangsan"
             class="layui-input" value="{{ $user->username ?? '' }}" {{ ($user && $user->username === 'admin') ? 'readonly' : '' }} />
    </div>

    <div class="sage-field">
      <label class="sage-field-label">显示姓名 / 昵称<span class="req">*</span></label>
      <input type="text" name="name" lay-verify="required" placeholder="例如：张三"
             class="layui-input" value="{{ $user->name ?? '' }}" />
    </div>

    <div class="sage-field">
      <label class="sage-field-label">电子邮箱（唯一）<span class="req">*</span></label>
      <input type="email" name="email" lay-verify="required|email" placeholder="example@domain.com"
             class="layui-input" value="{{ $user->email ?? '' }}" />
    </div>

    @if(!$user)
    <div class="sage-field">
      <label class="sage-field-label">初始密码<span class="req">*</span></label>
      <input type="password" name="password" lay-verify="required" placeholder="至少 6 位字符" class="layui-input" />
    </div>
    @endif

    <div class="sage-grid-2">
      <div class="sage-field">
        <label class="sage-field-label">账号状态</label>
        <select name="status">
          <option value="1" {{ (!$user || $user->status === 1) ? 'selected' : '' }}>启用</option>
          <option value="0" {{ ($user && $user->status === 0) ? 'selected' : '' }}>禁用</option>
        </select>
      </div>
      <div class="sage-field">
        <label class="sage-field-label">后台管理权限</label>
        <div style="padding-top: 4px;">
          <input type="checkbox" name="is_admin" value="1" lay-skin="switch" lay-text="管理员|普通"
                 {{ (!$user || $user->is_admin) ? 'checked' : '' }} />
        </div>
      </div>
    </div>

    <div class="sage-form-footer">
      <button type="button" onclick="AdminUI.closeSelf()" class="layui-btn layui-btn-primary">取消</button>
      <button type="submit" lay-submit lay-filter="saveUser" class="layui-btn">保存用户</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
layui.use(['form'], function () {
  var form = layui.form;

  form.on('submit(saveUser)', function (data) {
    var payload = Object.assign({}, data.field, {
      is_admin: data.field.is_admin ? true : false
    });

    AdminUI.request('{{ $user ? route("admin.users.update", $user, false) : route("admin.users.store", [], false) }}', {
      method: '{{ $user ? "PUT" : "POST" }}',
      data: payload
    }).then(function (res) {
      if (window.parent && window.parent.AdminUI) {
        window.parent.AdminUI.toast(res.message, 'success');
      }
      if (window.parent && window.parent.reloadUserTable) {
        window.parent.reloadUserTable();
      }
      AdminUI.closeSelf();
    }).catch(function (err) {
      AdminUI.toast(err.message, 'error');
    });

    return false;
  });
});
</script>
@endpush
