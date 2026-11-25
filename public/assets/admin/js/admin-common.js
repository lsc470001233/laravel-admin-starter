/**
 * ShopMate Admin — Layui 现代交互统一封装
 * 支持按场景灵活选用：右侧抽屉 (openDrawer) / 居中弹窗 (openModal) / 确认框 / 轻提示
 */
window.AdminUI = (function () {
  function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function request(url, options) {
    options = options || {};
    var method = (options.method || 'GET').toUpperCase();
    var headers = Object.assign({
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': getCsrfToken()
    }, options.headers || {});

    var fetchOpts = { method: method, headers: headers, credentials: 'same-origin' };
    if (options.data !== undefined && method !== 'GET') {
      headers['Content-Type'] = 'application/json';
      fetchOpts.body = JSON.stringify(options.data);
    }

    return fetch(url, fetchOpts).then(function (res) {
      return res.text().then(function (text) {
        var json = null;
        try {
          json = text ? JSON.parse(text) : {};
        } catch (e) {
          json = { message: res.status === 419 ? '页面会话已过期，请刷新后重试' : ('服务异常 (HTTP ' + res.status + ')') };
        }
        if (!res.ok) {
          var msg = (json && json.message) || ('请求失败 (HTTP ' + res.status + ')');
          if (json && json.errors) {
            var firstKey = Object.keys(json.errors)[0];
            if (firstKey && json.errors[firstKey][0]) {
              msg = json.errors[firstKey][0];
            }
          }
          var err = new Error(msg);
          err.status = res.status;
          err.payload = json;
          throw err;
        }
        return json;
      });
    });
  }

  function toast(msg, type) {
    var iconMap = { success: 1, error: 2, warn: 0, info: 6 };
    layui.layer.msg(msg, {
      icon: iconMap[type] !== undefined ? iconMap[type] : 1,
      time: 2200
    });
  }

  function confirm(msg, onOk) {
    layui.layer.confirm(msg, {
      title: '操作确认',
      skin: 'sage-modal',
      btn: ['确认执行', '取消'],
      shadeClose: true
    }, function (index) {
      layui.layer.close(index);
      if (typeof onOk === 'function') onOk();
    });
  }

  // 右侧抽屉（适合字段较多、不希望遮挡左侧列表的编辑表单）
  function openDrawer(opts) {
    return layui.layer.open({
      type: 2,
      title: opts.title || '编辑信息',
      skin: 'sage-drawer',
      area: [opts.width || '460px', '100%'],
      offset: 'r',
      anim: 'slideLeft',
      shade: 0.25,
      shadeClose: true,
      content: opts.url,
      end: opts.end
    });
  }

  // 重设计居中弹窗（适合轻量表单、新增字典项、重置密码、发送测试邮件）
  function openModal(opts) {
    return layui.layer.open({
      type: 2,
      title: opts.title || '操作窗口',
      skin: 'sage-modal',
      area: [opts.width || '500px', opts.height || '420px'],
      shade: 0.3,
      shadeClose: true,
      content: opts.url,
      end: opts.end
    });
  }

  function closeSelf() {
    if (window.parent && window.parent.layui && window.parent.layui.layer) {
      var idx = window.parent.layui.layer.getFrameIndex(window.name);
      window.parent.layui.layer.close(idx);
    }
  }

  function badgeHtml(label, color) {
    var tone = color || 'teal';
    return '<span class="sage-badge sage-badge-' + tone + '">' + layui.util.escape(label) + '</span>';
  }

  return {
    csrf: getCsrfToken,
    request: request,
    toast: toast,
    confirm: confirm,
    openDrawer: openDrawer,
    openModal: openModal,
    closeSelf: closeSelf,
    badgeHtml: badgeHtml
  };
})();
