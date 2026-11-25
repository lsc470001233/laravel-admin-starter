# 01 - Layui 护眼青灰主题与交互规范

## 多套主题配色（`public/assets/admin/css/layui-sage-modern.css`）
系统内置 **10 套精选企业后台主题配色**，可在「系统设置 -> 后台主题配色」中点击实时预览并持久化保存（存入 `system_settings` 表 `group = 'appearance', key = 'admin_theme'`）：
1. **`sage`（护眼青灰，默认）**：深青 `#0F766E` + 护眼浅青灰侧栏与画布
2. **`blue`（极简企业蓝）**：皇家蓝顶栏 `#1E40AF` + 浅灰侧栏 + 科技蓝主色 `#2563EB`
3. **`slate`（深炭商务 Pro）**：深炭黑侧栏 `#0F172A` + 纯白顶栏 + 亮蓝主色 `#2563EB`
4. **`fluent`（现代桌面 Fluent）**：纯白轻盈侧栏 + 浅灰画布 + 靛蓝主色 `#4F46E5`
5. **`emerald`（森林翠绿）**：深墨绿侧栏 `#064E3B` + 翠绿主色 `#059669`
6. **`navy`（深海科技蓝）**：深海蓝侧栏 `#0C233C` + 湖蓝主色 `#0284C7`
7. **`amber`（暖阳琥珀）**：柔和暖白画布 `#F7F4EF` + 琥珀橙主色 `#D97706`
8. **`rose`（绯红玫瑰）**：深梅灰侧栏 `#1F1622` + 玫瑰红主色 `#E11D48`
9. **`violet`（极光紫韵）**：深靛紫顶栏 `#4C1D95` + 浅紫灰侧栏 + 亮紫主色 `#7C3AED`
10. **`graphite`（极简石墨黑）**：极致黑白灰高定极简风（石墨黑 `#18181B`）

`AppServiceProvider` 自动向全部 `admin.*` 视图注入 `$adminTheme`，切换时通过 `window.parent.AdminTheme.apply(theme)` 无刷新同步更新主框架与全部已打开 `iframe` 标签页。

## 三种交互模式的选用原则
系统不强制单一交互方式，由 `AdminUI`（`public/assets/admin/js/admin-common.js`）提供统一封装，按操作习惯选用：

1. **上下主从分屏**（`.sage-split-page`）
   - 适用场景：需要同时查看主记录与关联明细（如**数据字典**的字典类型与字典项、后续的订单与订单明细）。
   - 支持中间拖拽手柄动态调节上下高度。

2. **右侧抽屉编辑**（`AdminUI.openDrawer({ title, url, width })`）
   - 适用场景：字段较多、编辑时希望参考左侧主表数据的场景（如**用户管理**的新增与编辑）。
   - 基于 Layui `layer.open({ offset: 'r', anim: 'slideLeft', skin: 'sage-drawer' })`。

3. **重设计居中弹窗**（`AdminUI.openModal({ title, url, width, height })`）
   - 适用场景：字段精简、聚焦确认的轻量操作（如**重置密码**、**新增/编辑字典类型或字典项**、**发送测试邮件**）。
   - 基于 Layui `layer.open({ skin: 'sage-modal' })`，统一纯白标题栏、10px 圆角与柔和弥散阴影。
