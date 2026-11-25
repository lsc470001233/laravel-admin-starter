@extends('admin.layouts.page')

@section('title', $dictType ? '编辑字典类型' : '新增字典类型')

@section('content')
<div class="sage-form-box">
  <form class="layui-form">
    <div class="sage-field">
      <label class="sage-field-label">字典名称<span class="req">*</span></label>
      <input type="text" name="name" lay-verify="required" placeholder="例如：订单状态" class="layui-input" value="{{ $dictType->name ?? '' }}" />
    </div>

    <div class="sage-grid-2">
      <div class="sage-field">
        <label class="sage-field-label">字典编码（唯一）<span class="req">*</span></label>
        <input type="text" name="code" lay-verify="required" placeholder="例如：order_status" class="layui-input" value="{{ $dictType->code ?? '' }}" />
      </div>
      <div class="sage-field">
        <label class="sage-field-label">状态</label>
        <select name="status">
          <option value="1" {{ (!$dictType || $dictType->status === 1) ? 'selected' : '' }}>启用</option>
          <option value="0" {{ ($dictType && $dictType->status === 0) ? 'selected' : '' }}>停用</option>
        </select>
      </div>
    </div>

    <div class="sage-field">
      <label class="sage-field-label">备注说明</label>
      <textarea name="remark" placeholder="选填，说明该字典类型的用途" class="layui-textarea" style="min-height: 76px;">{{ $dictType->remark ?? '' }}</textarea>
    </div>

    <div class="sage-form-footer">
      <button type="button" onclick="AdminUI.closeSelf()" class="layui-btn layui-btn-primary">取消</button>
      <button type="submit" lay-submit lay-filter="saveType" class="layui-btn">保存</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
layui.use(['form'], function () {
  var form = layui.form;

  form.on('submit(saveType)', function (data) {
    AdminUI.request('{{ $dictType ? route("admin.dicts.types.update", $dictType, false) : route("admin.dicts.types.store", [], false) }}', {
      method: '{{ $dictType ? "PUT" : "POST" }}',
      data: data.field
    }).then(function (res) {
      if (window.parent && window.parent.AdminUI) window.parent.AdminUI.toast(res.message, 'success');
      if (window.parent && window.parent.reloadDictTypes) window.parent.reloadDictTypes();
      AdminUI.closeSelf();
    }).catch(function (err) {
      AdminUI.toast(err.message, 'error');
    });
    return false;
  });
});
</script>
@endpush
