@extends('admin.layouts.page')

@section('title', '系统设置')

@push('styles')
<style>
  .setting-wrap { max-width: 1060px; display: flex; flex-direction: column; gap: 12px; }
  .setting-head {
    padding: 12px 18px;
    border-bottom: 1px solid var(--sage-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .setting-title { font-size: 14px; font-weight: 600; color: var(--sage-text); }
  .setting-sub { font-size: 12px; color: var(--sage-text-muted); margin-top: 2px; }
  .setting-body { padding: 18px 20px; }
  .setting-foot {
    padding: 12px 20px;
    background: var(--sage-table-foot);
    border-top: 1px solid var(--sage-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  /* 主题选择卡片 */
  .theme-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(186px, 1fr));
    gap: 12px;
  }
  .theme-card {
    border: 2px solid var(--sage-border);
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    background: #ffffff;
    transition: all 0.15s;
    position: relative;
  }
  .theme-card:hover {
    border-color: var(--sage-primary);
    transform: translateY(-1px);
  }
  .theme-card.active {
    border-color: var(--sage-primary);
    box-shadow: 0 0 0 3px var(--sage-primary-ring);
  }
  .theme-preview {
    height: 76px;
    display: flex;
    flex-direction: column;
    border-bottom: 1px solid #e2e8f0;
  }
  .tp-head { height: 18px; display: flex; align-items: center; padding: 0 8px; gap: 4px; border-bottom: 1px solid rgba(0,0,0,0.06); }
  .tp-dot { width: 6px; height: 6px; border-radius: 50%; }
  .tp-body { flex: 1; display: flex; }
  .tp-side { width: 30%; padding: 6px 5px; display: flex; flex-direction: column; gap: 4px; border-right: 1px solid rgba(0,0,0,0.06); }
  .tp-bar { height: 5px; border-radius: 2px; opacity: 0.7; }
  .tp-main { flex: 1; padding: 7px; display: flex; flex-direction: column; gap: 5px; }
  .tp-box { background: #ffffff; border-radius: 3px; flex: 1; border: 1px solid rgba(0,0,0,0.06); padding: 4px; display: flex; align-items: flex-start; }
  .tp-btn { width: 28px; height: 7px; border-radius: 2px; }
  .theme-info { padding: 10px 11px; }
  .theme-name { font-size: 13px; font-weight: 600; color: #0f172a; display: flex; align-items: center; justify-content: space-between; }
  .theme-desc { font-size: 11.5px; color: #64748b; margin-top: 4px; line-height: 1.45; }
  .theme-check {
    display: none;
    font-size: 11px;
    padding: 1px 6px;
    border-radius: 999px;
    background: var(--sage-primary);
    color: #fff;
    font-weight: 500;
  }
  .theme-card.active .theme-check { display: inline-block; }

  @media (max-width: 780px) {
    .theme-grid { grid-template-columns: repeat(2, 1fr); }
  }
</style>
@endpush

@section('content')
<div class="sage-page">
  <div class="setting-wrap">

    <!-- ================= 卡片 1：主题配色设置 ================= -->
    <div class="sage-card">
      <div class="setting-head">
        <div>
          <div class="setting-title">后台主题配色</div>
          <div class="setting-sub">点击下方任意主题卡片可实时预览，保存后全局持久化生效（含登录页与所有子页面）</div>
        </div>
      </div>

      <div class="setting-body">
        <div class="theme-grid" id="themeGrid">
          @foreach($themes as $key => $t)
            <div class="theme-card {{ $currentTheme === $key ? 'active' : '' }}" data-theme-key="{{ $key }}">
              <div class="theme-preview">
                <div class="tp-head" style="background: {{ $t['header'] }};">
                  <span class="tp-dot" style="background: {{ $t['primary'] }};"></span>
                  <span class="tp-bar" style="width: 34px; background: {{ $t['header'] === '#ffffff' ? '#cbd5e1' : '#ffffff' }};"></span>
                </div>
                <div class="tp-body">
                  <div class="tp-side" style="background: {{ $t['side'] }};">
                    <span class="tp-bar" style="width: 100%; background: {{ $key === 'graphite' ? '#ffffff' : $t['primary'] }}; opacity: 0.92;"></span>
                    <span class="tp-bar" style="width: 75%; background: {{ !empty($t['dark_side']) ? 'rgba(255,255,255,0.32)' : '#cbd5e1' }};"></span>
                    <span class="tp-bar" style="width: 85%; background: {{ !empty($t['dark_side']) ? 'rgba(255,255,255,0.24)' : '#cbd5e1' }};"></span>
                  </div>
                  <div class="tp-main" style="background: {{ $t['canvas'] }};">
                    <div class="tp-box">
                      <span class="tp-btn" style="background: {{ $t['primary'] }};"></span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="theme-info">
                <div class="theme-name">
                  <span>{{ $t['name'] }}</span>
                  <span class="theme-check">当前</span>
                </div>
                <div class="theme-desc">{{ $t['desc'] }}</div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="setting-foot">
        <span style="font-size: 12px; color: var(--sage-text-muted);">提示：切换主题时会同步更新外层主框架与全部已打开标签页。</span>
        <button type="button" id="saveThemeBtn" class="layui-btn">
          <i class="layui-icon layui-icon-theme"></i> 保存主题配色
        </button>
      </div>
    </div>

    <!-- ================= 卡片 2：SMTP 邮件服务设置 ================= -->
    <div class="sage-card">
      <div class="setting-head">
        <div>
          <div class="setting-title">SMTP 邮件服务设置</div>
          <div class="setting-sub">配置持久化存储在数据库 system_settings 表，发信时自动动态生效</div>
        </div>
        <button type="button" id="openTestMailBtn" class="layui-btn layui-btn-primary">
          <i class="layui-icon layui-icon-release"></i> 发送测试邮件
        </button>
      </div>

      <form class="layui-form" lay-filter="smtpForm">
        <div class="setting-body">
          <div class="sage-grid-2">
            <div class="sage-field">
              <label class="sage-field-label">SMTP 服务器地址<span class="req">*</span></label>
              <input type="text" name="smtp_host" lay-verify="required" placeholder="例如：smtp.qq.com 或 smtp.exmail.qq.com"
                     class="layui-input" value="{{ $smtp['smtp_host'] }}" />
            </div>
            <div class="sage-field">
              <label class="sage-field-label">SMTP 端口<span class="req">*</span></label>
              <input type="number" name="smtp_port" lay-verify="required|number" placeholder="SSL 常用 465，TLS 常用 587"
                     class="layui-input" value="{{ $smtp['smtp_port'] }}" />
            </div>
          </div>

          <div class="sage-grid-2">
            <div class="sage-field">
              <label class="sage-field-label">加密协议<span class="req">*</span></label>
              <select name="smtp_encryption">
                <option value="ssl" {{ $smtp['smtp_encryption'] === 'ssl' ? 'selected' : '' }}>SSL (推荐 465 端口)</option>
                <option value="tls" {{ $smtp['smtp_encryption'] === 'tls' ? 'selected' : '' }}>TLS / STARTTLS (587 端口)</option>
                <option value="none" {{ $smtp['smtp_encryption'] === 'none' ? 'selected' : '' }}>不加密 (25 端口)</option>
              </select>
            </div>
            <div class="sage-field">
              <label class="sage-field-label">SMTP 登录账号<span class="req">*</span></label>
              <input type="text" name="smtp_username" lay-verify="required" placeholder="一般为发件邮箱账号"
                     class="layui-input" value="{{ $smtp['smtp_username'] }}" />
            </div>
          </div>

          <div class="sage-field">
            <label class="sage-field-label">SMTP 密码 / 授权码</label>
            <input type="password" name="smtp_password" autocomplete="new-password"
                   placeholder="{{ !empty($smtp['smtp_password']) ? '已保存（留空则保持原密码不变）' : '请输入邮箱密码或 SMTP 授权码' }}"
                   class="layui-input" />
          </div>

          <div class="sage-grid-2">
            <div class="sage-field">
              <label class="sage-field-label">发件人邮箱地址<span class="req">*</span></label>
              <input type="email" name="smtp_from_address" lay-verify="required|email" placeholder="noreply@domain.com"
                     class="layui-input" value="{{ $smtp['smtp_from_address'] }}" />
            </div>
            <div class="sage-field">
              <label class="sage-field-label">发件人显示名称<span class="req">*</span></label>
              <input type="text" name="smtp_from_name" lay-verify="required" placeholder="ShopMate 系统通知"
                     class="layui-input" value="{{ $smtp['smtp_from_name'] }}" />
            </div>
          </div>
        </div>

        <div class="setting-foot">
          <span style="font-size: 12px; color: var(--sage-text-muted);">提示：修改配置后请先点击「保存 SMTP 配置」，再点击「发送测试邮件」验证连通性。</span>
          <button type="submit" lay-submit lay-filter="saveSmtp" class="layui-btn">
            <i class="layui-icon layui-icon-ok"></i> 保存 SMTP 配置
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
layui.use(['form'], function () {
  var form = layui.form;
  var selectedTheme = '{{ $currentTheme }}';

  function previewTheme(theme) {
    selectedTheme = theme;
    document.documentElement.setAttribute('data-theme', theme);
    document.querySelectorAll('.theme-card').forEach(function (card) {
      card.classList.toggle('active', card.getAttribute('data-theme-key') === theme);
    });
    if (window.parent && window.parent !== window) {
      if (window.parent.AdminTheme && typeof window.parent.AdminTheme.apply === 'function') {
        window.parent.AdminTheme.apply(theme);
      } else {
        try {
          window.parent.document.documentElement.setAttribute('data-theme', theme);
          window.parent.document.querySelectorAll('.sage-iframe').forEach(function (f) {
            if (f.contentDocument && f.contentDocument.documentElement) {
              f.contentDocument.documentElement.setAttribute('data-theme', theme);
            }
          });
        } catch (e) {}
      }
    }
  }

  document.querySelectorAll('.theme-card').forEach(function (card) {
    card.addEventListener('click', function () {
      previewTheme(card.getAttribute('data-theme-key'));
    });
  });

  document.getElementById('saveThemeBtn').addEventListener('click', function () {
    AdminUI.request('{{ route("admin.settings.theme.save", [], false) }}', {
      method: 'POST',
      data: { admin_theme: selectedTheme }
    }).then(function (res) {
      previewTheme(res.theme || selectedTheme);
      AdminUI.toast(res.message, 'success');
    }).catch(function (err) {
      AdminUI.toast(err.message, 'error');
    });
  });

  form.on('submit(saveSmtp)', function (data) {
    AdminUI.request('{{ route("admin.settings.smtp.save", [], false) }}', {
      method: 'POST',
      data: data.field
    }).then(function (res) {
      AdminUI.toast(res.message, 'success');
    }).catch(function (err) {
      AdminUI.toast(err.message, 'error');
    });
    return false;
  });

  // 发送测试邮件：使用重设计居中弹窗
  document.getElementById('openTestMailBtn').addEventListener('click', function () {
    AdminUI.openModal({
      title: '发送测试邮件',
      url: '{{ route("admin.settings.smtp.test_modal", [], false) }}',
      width: '460px',
      height: '340px'
    });
  });
});
</script>
@endpush
