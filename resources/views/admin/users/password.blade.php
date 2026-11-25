@extends('admin.layouts.page')

@section('title', '重置密码')

@section('content')
<div class="sage-form-box">
  <div class="sage-form-tip">正在为用户 <strong>{{ $user->username }}</strong>（{{ $user->email }}）重置登录密码。</div>

  <form class="layui-form">
    <div class="sage-field">
      <label class="sage-field-label">新密码<span class="req">*</span></label>
      <input type="password" name="password" lay-verify="required" placeholder="请输入至少 6 位新密码" class="layui-input" />
    </div>

    <div class="sage-field">
      <label class="sage-field-label">确认新密码<span class="req">*</span></label>
      <input type="password" name="password_confirmation" lay-verify="required" placeholder="请再次输入新密码" class="layui-input" />
    </div>

    <div class="sage-form-footer">
      <button type="button" onclick="AdminUI.closeSelf()" class="layui-btn layui-btn-primary">取消</button>
      <button type="submit" lay-submit lay-filter="savePwd" class="layui-btn">确认修改</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
layui.use(['form'], function () {
  var form = layui.form;

  form.on('submit(savePwd)', function (data) {
    AdminUI.request('{{ route("admin.users.password_update", $user, false) }}', {
      method: 'PUT',
      data: data.field
    }).then(function (res) {
      if (window.parent && window.parent.AdminUI) {
        window.parent.AdminUI.toast(res.message, 'success');
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
