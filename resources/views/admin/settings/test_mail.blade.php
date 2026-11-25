@extends('admin.layouts.page')

@section('title', '发送测试邮件')

@section('content')
<div class="sage-form-box">
  <div class="sage-form-tip">将使用当前已保存的 SMTP 配置实时向目标邮箱发送一封测试邮件。</div>

  <form class="layui-form">
    <div class="sage-field">
      <label class="sage-field-label">收件人邮箱<span class="req">*</span></label>
      <input type="email" name="to_email" lay-verify="required|email" placeholder="请输入接收测试邮件的邮箱"
             class="layui-input" value="{{ $defaultEmail }}" />
    </div>

    <div class="sage-field">
      <label class="sage-field-label">邮件主题</label>
      <input type="text" name="subject" placeholder="ShopMate SMTP 测试邮件"
             class="layui-input" value="ShopMate SMTP 连通性测试邮件" />
    </div>

    <div id="testResultBox" style="display:none; padding: 10px 12px; border-radius: 6px; font-size: 12px; line-height: 1.5; margin-top: 8px;"></div>

    <div class="sage-form-footer">
      <button type="button" onclick="AdminUI.closeSelf()" class="layui-btn layui-btn-primary">关闭</button>
      <button type="submit" lay-submit lay-filter="sendTest" id="sendBtn" class="layui-btn">立即发送</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
layui.use(['form'], function () {
  var form = layui.form;

  form.on('submit(sendTest)', function (data) {
    var btn = document.getElementById('sendBtn');
    var box = document.getElementById('testResultBox');
    btn.disabled = true;
    btn.textContent = '发送中...';
    box.style.display = 'none';

    AdminUI.request('{{ route("admin.settings.smtp.test", [], false) }}', {
      method: 'POST',
      data: data.field
    }).then(function (res) {
      box.style.display = 'block';
      box.style.background = '#ecfdf5';
      box.style.border = '1px solid #a7f3d0';
      box.style.color = '#065f46';
      box.textContent = res.message;
      AdminUI.toast('测试邮件发送成功', 'success');
    }).catch(function (err) {
      box.style.display = 'block';
      box.style.background = '#fef2f2';
      box.style.border = '1px solid #fecaca';
      box.style.color = '#991b1b';
      box.textContent = err.message;
      AdminUI.toast('测试邮件发送失败', 'error');
    }).finally(function () {
      btn.disabled = false;
      btn.textContent = '立即发送';
    });

    return false;
  });
});
</script>
@endpush
