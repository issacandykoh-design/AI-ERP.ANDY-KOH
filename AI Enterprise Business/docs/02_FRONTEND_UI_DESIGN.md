# 前端界面架构设计 - 类似ChatGPT风格

## 1. 整体布局设计

### 1.1 页面布局结构
```
┌─────────────────────────────────────────────────────────────┐
│                    Header (顶部导航栏)                        │
├─────────────────┬───────────────────────────────────────────┤
│                 │                                           │
│   Sidebar       │            Main Content                   │
│   (左侧栏)       │            (主内容区)                      │
│                 │                                           │
│  - 会话列表      │  ┌─────────────────────────────────────┐  │
│  - 新建会话      │  │        Mode Selector                │  │
│  - 用户菜单      │  │        (模式切换器)                  │  │
│                 │  ├─────────────────────────────────────┤  │
│                 │  │                                     │  │
│                 │  │        Chat Area                    │  │
│                 │  │        (聊天区域)                    │  │
│                 │  │                                     │  │
│                 │  │                                     │  │
│                 │  ├─────────────────────────────────────┤  │
│                 │  │        Input Area                   │  │
│                 │  │        (输入区域)                    │  │
│                 │  └─────────────────────────────────────┘  │
│                 │                                           │
└─────────────────┴───────────────────────────────────────────┘
```

### 1.2 响应式设计
- **桌面端 (>1024px)**：左侧栏固定显示，宽度280px
- **平板端 (768px-1024px)**：左侧栏可收缩，点击展开
- **移动端 (<768px)**：左侧栏抽屉式，全屏覆盖

## 2. 组件架构设计

### 2.1 技术栈选择
```javascript
// 前端技术栈
{
  "framework": "React 18",
  "language": "TypeScript",
  "styling": "Tailwind CSS + Styled Components",
  "ui_library": "Ant Design + Custom Components",
  "state_management": "Zustand",
  "routing": "React Router v6",
  "http_client": "Axios",
  "websocket": "Socket.io-client",
  "charts": "Recharts + Chart.js",
  "icons": "Lucide React",
  "animations": "Framer Motion"
}
```

### 2.2 组件层次结构
```
src/
├── components/
│   ├── layout/
│   │   ├── Header.tsx              # 顶部导航栏
│   │   ├── Sidebar.tsx             # 左侧栏
│   │   ├── MainLayout.tsx          # 主布局
│   │   └── MobileDrawer.tsx        # 移动端抽屉
│   ├── chat/
│   │   ├── ChatArea.tsx            # 聊天区域
│   │   ├── MessageList.tsx         # 消息列表
│   │   ├── MessageItem.tsx         # 单条消息
│   │   ├── InputArea.tsx           # 输入区域
│   │   ├── ModeSelector.tsx        # 模式切换器
│   │   └── TypingIndicator.tsx     # 输入指示器
│   ├── session/
│   │   ├── SessionList.tsx         # 会话列表
│   │   ├── SessionItem.tsx         # 会话项
│   │   ├── NewSessionButton.tsx    # 新建会话按钮
│   │   └── SessionActions.tsx      # 会话操作
│   ├── export/
│   │   ├── ExportModal.tsx         # 导出弹窗
│   │   ├── ExportProgress.tsx      # 导出进度
│   │   └── DownloadButton.tsx      # 下载按钮
│   ├── user/
│   │   ├── UserProfile.tsx         # 用户资料
│   │   ├── UserMenu.tsx            # 用户菜单
│   │   └── UsageStats.tsx          # 使用统计
│   └── common/
│       ├── Loading.tsx             # 加载组件
│       ├── ErrorBoundary.tsx       # 错误边界
│       ├── Toast.tsx               # 提示组件
│       └── Modal.tsx               # 模态框
├── pages/
│   ├── ChatPage.tsx                # 聊天主页
│   ├── LoginPage.tsx               # 登录页
│   ├── RegisterPage.tsx            # 注册页
│   ├── UsagePage.tsx               # 使用统计页
│   └── SettingsPage.tsx            # 设置页
├── hooks/
│   ├── useAuth.ts                  # 认证钩子
│   ├── useChat.ts                  # 聊天钩子
│   ├── useSession.ts               # 会话钩子
│   └── useExport.ts                # 导出钩子
├── stores/
│   ├── authStore.ts                # 认证状态
│   ├── chatStore.ts                # 聊天状态
│   ├── sessionStore.ts             # 会话状态
│   └── uiStore.ts                  # UI状态
├── services/
│   ├── api.ts                      # API服务
│   ├── auth.ts                     # 认证服务
│   ├── chat.ts                     # 聊天服务
│   └── export.ts                   # 导出服务
└── utils/
    ├── constants.ts                # 常量
    ├── helpers.ts                  # 工具函数
    └── types.ts                    # 类型定义
```

## 3. 详细组件设计

### 3.1 Header组件 (顶部导航栏)
```typescript
interface HeaderProps {
  user: User | null;
  onMenuToggle: () => void;
}

// 功能特性
- 应用Logo和标题
- 用户头像和下拉菜单
- 移动端菜单切换按钮
- 主题切换按钮（预留）
- 通知中心（预留）
```

### 3.2 Sidebar组件 (左侧栏)
```typescript
interface SidebarProps {
  sessions: ChatSession[];
  currentSessionId: string | null;
  isCollapsed: boolean;
  onSessionSelect: (sessionId: string) => void;
  onNewSession: () => void;
}

// 功能特性
- 新建会话按钮（突出显示）
- 会话列表（按时间倒序）
- 会话搜索功能
- 会话重命名/删除
- 用户资料和设置入口
- 使用统计入口
```

### 3.3 ModeSelector组件 (模式切换器)
```typescript
interface ModeSelectorProps {
  currentMode: ExportMode;
  onModeChange: (mode: ExportMode) => void;
}

type ExportMode = 'pdf' | 'csv' | 'txt' | 'png';

// 设计规格
- 位置：聊天区域顶部
- 样式：标签页形式
- 颜色：白色背景，选中状态蓝色高亮
- 图标：每种模式对应图标
- 动画：切换时平滑过渡
```

### 3.4 ChatArea组件 (聊天区域)
```typescript
interface ChatAreaProps {
  messages: Message[];
  isLoading: boolean;
  currentMode: ExportMode;
}

// 功能特性
- 消息列表滚动
- 自动滚动到底部
- 消息时间戳
- 消息状态指示
- 导出文件预览
- 错误消息处理
```

### 3.5 InputArea组件 (输入区域)
```typescript
interface InputAreaProps {
  onSendMessage: (message: string) => void;
  isLoading: boolean;
  placeholder?: string;
}

// 功能特性
- 多行文本输入
- 发送按钮
- 字符计数
- 快捷键支持 (Ctrl+Enter)
- 输入历史记录
- 文件上传（预留）
```

## 4. 样式设计规范

### 4.1 色彩方案 (白色主题)
```css
:root {
  /* 主色调 */
  --primary-color: #3b82f6;      /* 蓝色 */
  --primary-hover: #2563eb;      /* 蓝色悬停 */
  --primary-light: #dbeafe;      /* 浅蓝色 */
  
  /* 背景色 */
  --bg-primary: #ffffff;         /* 主背景 */
  --bg-secondary: #f8fafc;       /* 次要背景 */
  --bg-tertiary: #f1f5f9;        /* 第三背景 */
  
  /* 文字色 */
  --text-primary: #1e293b;       /* 主文字 */
  --text-secondary: #64748b;     /* 次要文字 */
  --text-tertiary: #94a3b8;      /* 第三文字 */
  
  /* 边框色 */
  --border-primary: #e2e8f0;     /* 主边框 */
  --border-secondary: #cbd5e1;   /* 次要边框 */
  
  /* 状态色 */
  --success-color: #10b981;      /* 成功 */
  --warning-color: #f59e0b;      /* 警告 */
  --error-color: #ef4444;        /* 错误 */
  --info-color: #3b82f6;         /* 信息 */
}
```

### 4.2 布局规范
```css
/* 间距系统 */
--spacing-xs: 4px;
--spacing-sm: 8px;
--spacing-md: 16px;
--spacing-lg: 24px;
--spacing-xl: 32px;
--spacing-2xl: 48px;

/* 圆角系统 */
--radius-sm: 4px;
--radius-md: 8px;
--radius-lg: 12px;
--radius-xl: 16px;

/* 阴影系统 */
--shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
--shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
--shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
```

### 4.3 字体规范
```css
/* 字体大小 */
--text-xs: 12px;
--text-sm: 14px;
--text-base: 16px;
--text-lg: 18px;
--text-xl: 20px;
--text-2xl: 24px;
--text-3xl: 30px;

/* 字体权重 */
--font-normal: 400;
--font-medium: 500;
--font-semibold: 600;
--font-bold: 700;

/* 行高 */
--leading-tight: 1.25;
--leading-normal: 1.5;
--leading-relaxed: 1.625;
```

## 5. 交互设计

### 5.1 动画效果
```typescript
// 页面切换动画
const pageTransition = {
  initial: { opacity: 0, x: 20 },
  animate: { opacity: 1, x: 0 },
  exit: { opacity: 0, x: -20 },
  transition: { duration: 0.2 }
};

// 消息出现动画
const messageAnimation = {
  initial: { opacity: 0, y: 10 },
  animate: { opacity: 1, y: 0 },
  transition: { duration: 0.3 }
};

// 模式切换动画
const modeTransition = {
  layout: true,
  transition: { duration: 0.2 }
};
```

### 5.2 响应式交互
```typescript
// 触摸设备优化
- 增大点击区域 (最小44px)
- 支持滑动手势
- 优化滚动性能
- 防止误触

// 键盘快捷键
- Ctrl/Cmd + Enter: 发送消息
- Ctrl/Cmd + N: 新建会话
- Ctrl/Cmd + K: 搜索会话
- Esc: 关闭模态框
```

### 5.3 加载状态
```typescript
// 加载指示器类型
type LoadingState = 
  | 'idle'           // 空闲
  | 'sending'        // 发送中
  | 'processing'     // 处理中
  | 'generating'     // 生成中
  | 'exporting'      // 导出中
  | 'error';         // 错误

// 骨架屏设计
- 消息列表骨架屏
- 会话列表骨架屏
- 统计数据骨架屏
```

## 6. 可访问性设计

### 6.1 ARIA标签
```html
<!-- 语义化标签 -->
<nav aria-label="会话导航">
<main aria-label="聊天主区域">
<button aria-label="发送消息" aria-describedby="input-help">

<!-- 状态提示 -->
<div aria-live="polite" aria-atomic="true">
<div role="status" aria-label="正在生成回复">
```

### 6.2 键盘导航
```typescript
// Tab顺序优化
- 逻辑的Tab导航顺序
- 焦点可见性指示
- 跳过链接功能
- 焦点陷阱管理
```

### 6.3 屏幕阅读器支持
```typescript
// 内容描述
- 图片alt文本
- 按钮描述性文本
- 表单标签关联
- 错误消息关联
```

## 7. 性能优化

### 7.1 代码分割
```typescript
// 路由级别分割
const ChatPage = lazy(() => import('./pages/ChatPage'));
const UsagePage = lazy(() => import('./pages/UsagePage'));

// 组件级别分割
const ExportModal = lazy(() => import('./components/export/ExportModal'));
```

### 7.2 虚拟化
```typescript
// 长列表虚拟化
- 会话列表虚拟滚动
- 消息列表虚拟滚动
- 大数据表格虚拟化
```

### 7.3 缓存策略
```typescript
// 数据缓存
- React Query缓存
- 本地存储缓存
- 图片懒加载
- 预加载关键资源
```

## 8. 测试策略

### 8.1 单元测试
```typescript
// 组件测试
- 渲染测试
- 交互测试
- 状态测试
- 属性测试
```

### 8.2 集成测试
```typescript
// 用户流程测试
- 登录流程
- 聊天流程
- 导出流程
- 会话管理
```

### 8.3 端到端测试
```typescript
// E2E测试场景
- 完整用户旅程
- 跨浏览器测试
- 移动端测试
- 性能测试
```