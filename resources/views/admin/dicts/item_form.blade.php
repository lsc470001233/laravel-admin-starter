@extends('admin.layouts.page')

@section('title', $item ? '编辑字典项' : '新增字典项')

@section('content')
<div class="sage-form-box">
  <div class="sage-form-tip">所属字典类型：<strong>{{ $dictType->name }}</strong> ({{ $dictType->code }})</div>

  <form class="layui-form">
    <div class="sage-grid-2">
      <div class="sage-field">
        <label class="sage-field-label">字典标签<span class="req">*</span></label>
        <input type="text" name="label" lay-verify="required" placeholder="例如：已支付" class="layui-input" value="{{ $item->label ?? '' }}" />
      </div>
      <div class="sage-field">
        <label class="sage-field-label">字典键值（唯一）<span class="req">*</span></label>
        <input type="text" name="value" lay-verify="required" placeholder="例如：paid 或 1" class="layui-input" value="{{ $item->value ?? '' }}" />
      </div>
    </div>

    <div class="sage-grid-2">
      <div class="sage-field">
        <label class="sage-field-label">徽章色调</label>
        <select name="color">
          @foreach(['teal' => '青灰 (默认)', 'green' => '翠绿 (成功)', 'blue' => '天蓝 (信息)', 'amber' => '琥珀 (警告)', 'red' => '绯红 (危险)', 'gray' => '中性灰'] as $k => $v)
            <option value="{{ $k }}" {{ (($item->color ?? 'teal') === $k) ? 'selected' : '' }}>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div class="sage-field">
        <label class="sage-field-label">排序值（越小越靠前）</label>
        <input type="number" name="sort" lay-verify="required|number" class="layui-input" value="{{ $item->sort ?? 10 }}" />
      </div>
    </div>

    <div class="sage-field">
      <label class="sage-field-label">状态</label>
      <select name="status">
        <option value="1" {{ (!$item || $item->status === 1) ? 'selected' : '' }}>启用</option>
        <option value="0" {{ ($item && $item->status === 0) ? 'selected' : '' }}>停用</option>
      </select>
    </div>

    <div class="sage-field">
      <label class="sage-field-label">备注</label>
      <input type="text" name="remark" placeholder="选填" class="layui-input" value="{{ $item->remark ?? '' }}" />
    </div>

    <div class="sage-form-footer">
      <button type="button" onclick="AdminUI.closeSelf()" class="layui-btn layui-btn-primary">取消</button>
      <button type="submit" lay-submit lay-filter="saveItem" class="layui-btn">保存字典项</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
layui.use(['form'], function () {
  var form = layui.form;

  form.on('submit(saveItem)', function (data) {
    AdminUI.request('{{ $item ? route("admin.dicts.items.update", $item, false) : route("admin.dicts.items.store", $dictType, false) }}', {
      method: '{{ $item ? "PUT" : "POST" }}',
      data: data.field
    }).then(function (res) {
      if (window.parent && window.parent.AdminUI) window.parent.AdminUI.toast(res.message, 'success');
      if (window.parent && window.parent.reloadDictItems) window.parent.reloadDictItems();
      AdminUI.closeSelf();
    }).catch(function (err) {
      AdminUI.toast(err.message, 'error');
    });
    return false;
  });
});
</script>
@endpush
