@extends('admin.layouts.page')

@section('title', '数据字典')

@section('content')
<div class="sage-split-page" id="splitContainer">
  <!-- ================= 上半区：字典类型主表 ================= -->
  <div class="sage-card sage-split-top" id="topPane">
    <div class="sage-toolbar">
      <div class="sage-toolbar-left">
        <button type="button" id="addTypeBtn" class="layui-btn">
          <i class="layui-icon layui-icon-add-1"></i> 新增字典类型
        </button>
        <span class="sage-divider"></span>
        <input type="text" id="typeKeyword" placeholder="搜索字典名称 / 编码" class="layui-input" style="width: 190px; height: 30px;" />
        <button type="button" id="searchTypeBtn" class="layui-btn layui-btn-primary">查询</button>
        <button type="button" id="refreshTypeBtn" class="layui-btn layui-btn-primary">刷新</button>
      </div>
      <div class="sage-toolbar-right" style="font-size: 12px; color: var(--sage-primary); font-weight: 500;">
        主从分屏模式：点击上方字典类型，下方自动联动显示字典项
      </div>
    </div>
    <div style="flex: 1; min-height: 0;">
      <table id="dictTypeTable" lay-filter="dictTypeTable"></table>
    </div>
  </div>

  <!-- 可拖拽上下分隔手柄 -->
  <div class="sage-split-handle" id="splitHandle" title="上下拖动调节主从表高度"></div>

  <!-- ================= 下半区：字典项从表 ================= -->
  <div class="sage-card sage-split-bottom" id="bottomPane">
    <div class="sage-toolbar" style="background: var(--sage-table-foot);">
      <div class="sage-toolbar-left">
        <strong style="color: var(--sage-text); font-size: 13px;">字典项明细</strong>
        <span id="currentTypeLabel" style="font-size: 12px; color: var(--sage-text-muted);">请先选择上方字典类型</span>
      </div>
      <div class="sage-toolbar-right">
        <button type="button" id="addItemBtn" class="layui-btn layui-btn-xs" disabled>
          <i class="layui-icon layui-icon-add-1"></i> 新增字典项
        </button>
        <button type="button" id="refreshItemBtn" class="layui-btn layui-btn-primary layui-btn-xs">刷新明细</button>
      </div>
    </div>
    <div style="flex: 1; min-height: 0;">
      <table id="dictItemTable" lay-filter="dictItemTable"></table>
    </div>
  </div>
</div>

<script type="text/html" id="typeRowOps">
  <a class="sage-op" lay-event="edit">编辑</a>
  <span class="sage-op-sep">|</span>
  <a class="sage-op sage-op-danger" lay-event="del">删除</a>
</script>

<script type="text/html" id="itemRowOps">
  <a class="sage-op" lay-event="edit">编辑</a>
  <span class="sage-op-sep">|</span>
  <a class="sage-op sage-op-danger" lay-event="del">删除</a>
</script>
@endsection

@push('scripts')
<script>
layui.use(['table'], function () {
  var table = layui.table;
  var selectedType = null;

  var typeTable = table.render({
    elem: '#dictTypeTable',
    url: '{{ route("admin.dicts.types.list", [], false) }}',
    page: true,
    limit: 15,
    height: 'full-430',
    cols: [[
      { field: 'id', title: 'ID', width: 65, align: 'center' },
      { field: 'name', title: '字典名称', width: 180, templet: function (d) {
        return '<strong>' + layui.util.escape(d.name) + '</strong>';
      }},
      { field: 'code', title: '字典编码', width: 180, templet: function (d) {
        return '<code style="color:var(--sage-primary);font-weight:600;">' + layui.util.escape(d.code) + '</code>';
      }},
      { field: 'items_count', title: '明细数', width: 85, align: 'center' },
      { field: 'status', title: '状态', width: 90, align: 'center', templet: function (d) {
        return d.status === 1 ? AdminUI.badgeHtml('启用', 'green') : AdminUI.badgeHtml('停用', 'red');
      }},
      { field: 'remark', title: '备注说明', minWidth: 180 },
      { field: 'updated_at', title: '更新时间', width: 145 },
      { title: '操作', width: 110, fixed: 'right', toolbar: '#typeRowOps' }
    ]],
    done: function (res) {
      if (res.data && res.data.length > 0) {
        var target = selectedType
          ? (res.data.find(function (r) { return r.id === selectedType.id; }) || res.data[0])
          : res.data[0];
        selectTypeRow(target);
      } else {
        selectedType = null;
        document.getElementById('currentTypeLabel').textContent = '暂无字典类型';
        document.getElementById('addItemBtn').disabled = true;
        renderItems([]);
      }
    }
  });

  var itemTable = table.render({
    elem: '#dictItemTable',
    data: [],
    page: false,
    height: 'full-490',
    text: { none: '当前字典类型下暂无字典项，点击右上角“新增字典项”添加' },
    cols: [[
      { field: 'sort', title: '排序', width: 70, align: 'center' },
      { field: 'label', title: '字典标签', width: 170, templet: function (d) {
        return AdminUI.badgeHtml(d.label, d.color);
      }},
      { field: 'value', title: '字典键值', width: 140, templet: function (d) {
        return '<code style="color:var(--sage-primary);font-weight:600;">' + layui.util.escape(d.value) + '</code>';
      }},
      { field: 'color', title: '色调', width: 90, align: 'center' },
      { field: 'status', title: '状态', width: 90, align: 'center', templet: function (d) {
        return d.status === 1 ? AdminUI.badgeHtml('启用', 'green') : AdminUI.badgeHtml('停用', 'red');
      }},
      { field: 'remark', title: '备注', minWidth: 180 },
      { title: '操作', width: 110, fixed: 'right', toolbar: '#itemRowOps' }
    ]]
  });

  function renderItems(rows) {
    itemTable.reload({ data: rows, url: '' });
  }

  function loadItemsFor(type) {
    if (!type) return;
    AdminUI.request('/admin/dicts/types/' + type.id + '/items')
      .then(function (res) {
        renderItems(res.data || []);
      })
      .catch(function (err) {
        AdminUI.toast(err.message, 'error');
      });
  }

  function selectTypeRow(row) {
    selectedType = row;
    document.getElementById('currentTypeLabel').innerHTML =
      '当前联动：<strong style="color:var(--sage-primary);">' + layui.util.escape(row.name) + '</strong> (' + layui.util.escape(row.code) + ')';
    document.getElementById('addItemBtn').disabled = false;

    // 高亮选中行
    var view = document.querySelector('#topPane .layui-table-body');
    if (view) {
      view.querySelectorAll('tr').forEach(function (tr) { tr.classList.remove('sage-row-selected'); });
      var rows = table.cache.dictTypeTable || [];
      var idx = rows.findIndex(function (r) { return r.id === row.id; });
      if (idx >= 0) {
        var tr = view.querySelector('tr[data-index="' + idx + '"]');
        if (tr) tr.classList.add('sage-row-selected');
      }
    }

    loadItemsFor(row);
  }

  // 点击主表行自动联动从表
  table.on('row(dictTypeTable)', function (obj) {
    selectTypeRow(obj.data);
  });

  window.reloadDictTypes = function () {
    typeTable.reload();
  };
  window.reloadDictItems = function () {
    if (selectedType) {
      loadItemsFor(selectedType);
      typeTable.reload();
    }
  };

  document.getElementById('searchTypeBtn').addEventListener('click', function () {
    typeTable.reload({
      where: { keyword: document.getElementById('typeKeyword').value.trim() },
      page: { curr: 1 }
    });
  });
  document.getElementById('refreshTypeBtn').addEventListener('click', function () {
    document.getElementById('typeKeyword').value = '';
    typeTable.reload({ where: { keyword: '' }, page: { curr: 1 } });
  });
  document.getElementById('refreshItemBtn').addEventListener('click', function () {
    if (selectedType) loadItemsFor(selectedType);
  });

  // 新增字典类型（居中弹窗）
  document.getElementById('addTypeBtn').addEventListener('click', function () {
    AdminUI.openModal({
      title: '新增字典类型',
      url: '{{ route("admin.dicts.types.create", [], false) }}',
      width: '480px',
      height: '390px'
    });
  });

  // 新增字典项（居中弹窗）
  document.getElementById('addItemBtn').addEventListener('click', function () {
    if (!selectedType) return;
    AdminUI.openModal({
      title: '新增字典项 - ' + selectedType.name,
      url: '/admin/dicts/types/' + selectedType.id + '/items/create',
      width: '480px',
      height: '440px'
    });
  });

  // 主表行操作
  table.on('tool(dictTypeTable)', function (obj) {
    var d = obj.data;
    if (obj.event === 'edit') {
      AdminUI.openModal({
        title: '编辑字典类型：' + d.name,
        url: '/admin/dicts/types/' + d.id + '/edit',
        width: '480px',
        height: '390px'
      });
    } else if (obj.event === 'del') {
      AdminUI.confirm('删除字典类型 <strong>' + layui.util.escape(d.name) + '</strong> 将同时删除其下所有字典项，确认吗？', function () {
        AdminUI.request('/admin/dicts/types/' + d.id, { method: 'DELETE' })
          .then(function (res) {
            AdminUI.toast(res.message, 'success');
            selectedType = null;
            typeTable.reload();
          })
          .catch(function (err) { AdminUI.toast(err.message, 'error'); });
      });
    }
  });

  // 从表行操作
  table.on('tool(dictItemTable)', function (obj) {
    var d = obj.data;
    if (obj.event === 'edit') {
      AdminUI.openModal({
        title: '编辑字典项：' + d.label,
        url: '/admin/dicts/items/' + d.id + '/edit',
        width: '480px',
        height: '440px'
      });
    } else if (obj.event === 'del') {
      AdminUI.confirm('确认删除字典项 <strong>' + layui.util.escape(d.label) + '</strong> 吗？', function () {
        AdminUI.request('/admin/dicts/items/' + d.id, { method: 'DELETE' })
          .then(function (res) {
            AdminUI.toast(res.message, 'success');
            window.reloadDictItems();
          })
          .catch(function (err) { AdminUI.toast(err.message, 'error'); });
      });
    }
  });

  // 上下拖拽调节分屏比例
  var handle = document.getElementById('splitHandle');
  var topPane = document.getElementById('topPane');
  var bottomPane = document.getElementById('bottomPane');
  var container = document.getElementById('splitContainer');
  var dragging = false;
  handle.addEventListener('mousedown', function () { dragging = true; document.body.style.userSelect = 'none'; });
  window.addEventListener('mouseup', function () {
    if (!dragging) return;
    dragging = false;
    document.body.style.userSelect = '';
    table.resize('dictTypeTable');
    table.resize('dictItemTable');
  });
  window.addEventListener('mousemove', function (e) {
    if (!dragging) return;
    var rect = container.getBoundingClientRect();
    var ratio = (e.clientY - rect.top) / rect.height;
    ratio = Math.max(0.28, Math.min(0.72, ratio));
    topPane.style.flex = ratio;
    bottomPane.style.flex = 1 - ratio;
  });
});
</script>
@endpush
