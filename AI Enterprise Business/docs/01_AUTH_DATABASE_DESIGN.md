# 用户认证系统和MySQL数据库架构设计

## 1. 用户认证系统设计

### 1.1 JWT认证机制

#### JWT Token结构
```json
{
  "header": {
    "alg": "HS256",
    "typ": "JWT"
  },
  "payload": {
    "user_id": 123,
    "username": "user@example.com",
    "exp": 1640995200,
    "iat": 1640908800,
    "role": "user"
  },
  "signature": "..."
}
```

#### 认证流程
1. **用户注册/登录** → 验证凭据 → 生成JWT Token
2. **API请求** → 携带Bearer Token → 验证Token → 授权访问
3. **Token刷新** → 检查过期时间 → 自动刷新或重新登录

#### 安全措施
- Token过期时间：24小时
- 刷新Token：7天
- 密码加密：bcrypt + salt
- 防暴力破解：登录失败限制
- CORS配置：限制跨域访问

### 1.2 用户管理功能

#### 注册功能
- 邮箱验证
- 密码强度检查
- 用户名唯一性验证
- 账户激活机制

#### 登录功能
- 邮箱/用户名登录
- 记住登录状态
- 多设备登录管理
- 登录日志记录

#### 密码管理
- 密码重置（邮箱验证）
- 密码修改
- 登录设备管理
- 安全日志

## 2. MySQL数据库架构设计

### 2.1 数据库表结构

#### 用户表 (users)
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    avatar_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL,
    login_count INT DEFAULT 0,
    
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_created_at (created_at)
);
```

#### 聊天会话表 (chat_sessions)
```sql
CREATE TABLE chat_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    session_id VARCHAR(36) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_message_at TIMESTAMP NULL,
    message_count INT DEFAULT 0,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_created_at (created_at)
);
```

#### 聊天消息表 (chat_messages)
```sql
CREATE TABLE chat_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    session_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    message_type ENUM('user', 'assistant') NOT NULL,
    content TEXT NOT NULL,
    prompt_text TEXT,
    sql_query TEXT,
    query_result JSON,
    analysis_result TEXT,
    export_format ENUM('pdf', 'csv', 'txt', 'png') NULL,
    export_file_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processing_time_ms INT DEFAULT 0,
    
    FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_message_type (message_type)
);
```

#### 用户统计表 (user_statistics)
```sql
CREATE TABLE user_statistics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    total_sessions INT DEFAULT 0,
    total_messages INT DEFAULT 0,
    total_queries INT DEFAULT 0,
    total_exports INT DEFAULT 0,
    pdf_exports INT DEFAULT 0,
    csv_exports INT DEFAULT 0,
    txt_exports INT DEFAULT 0,
    png_exports INT DEFAULT 0,
    total_processing_time_ms BIGINT DEFAULT 0,
    avg_processing_time_ms DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_stats (user_id)
);
```

#### 导出文件表 (export_files)
```sql
CREATE TABLE export_files (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    session_id BIGINT NOT NULL,
    message_id BIGINT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type ENUM('pdf', 'csv', 'txt', 'png') NOT NULL,
    file_size BIGINT NOT NULL,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (message_id) REFERENCES chat_messages(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_file_type (file_type),
    INDEX idx_created_at (created_at)
);
```

#### 用户活动日志表 (user_activity_logs)
```sql
CREATE TABLE user_activity_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    activity_type ENUM('login', 'logout', 'query', 'export', 'session_create', 'session_delete') NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at)
);
```

### 2.2 数据库索引优化

#### 主要索引策略
1. **用户查询优化**
   - 用户ID复合索引
   - 邮箱和用户名唯一索引
   - 创建时间索引

2. **会话查询优化**
   - 用户ID + 创建时间复合索引
   - 会话ID唯一索引
   - 活跃状态索引

3. **消息查询优化**
   - 会话ID + 创建时间复合索引
   - 用户ID + 消息类型复合索引
   - 导出格式索引

### 2.3 数据隔离策略

#### 用户数据隔离
1. **行级安全**：所有查询都必须包含user_id条件
2. **API层隔离**：JWT Token验证用户身份
3. **数据库层隔离**：外键约束确保数据完整性
4. **应用层隔离**：中间件验证用户权限

#### 会话隔离
1. **会话归属**：每个会话只属于一个用户
2. **消息隔离**：消息只能在对应会话中访问
3. **导出隔离**：导出文件只能由创建者访问

### 2.4 数据备份和恢复

#### 备份策略
1. **全量备份**：每日凌晨自动备份
2. **增量备份**：每小时增量备份
3. **日志备份**：实时binlog备份
4. **异地备份**：云存储备份

#### 恢复策略
1. **点对点恢复**：基于binlog的精确恢复
2. **表级恢复**：单表数据恢复
3. **用户级恢复**：单用户数据恢复

## 3. 性能优化

### 3.1 数据库优化
- 连接池配置：最大连接数100
- 查询缓存：启用查询结果缓存
- 索引优化：定期分析和优化索引
- 分区策略：按时间分区大表

### 3.2 缓存策略
- Redis缓存：用户会话、统计数据
- 应用缓存：频繁查询结果
- CDN缓存：静态资源和导出文件

### 3.3 监控和告警
- 数据库性能监控
- 慢查询日志分析
- 连接数监控
- 存储空间监控

## 4. 安全考虑

### 4.1 数据安全
- 敏感数据加密存储
- 数据传输SSL加密
- 定期安全审计
- 访问日志记录

### 4.2 权限控制
- 最小权限原则
- 角色基础访问控制
- API访问限制
- 数据访问审计

### 4.3 合规性
- GDPR数据保护
- 用户数据删除权
- 数据导出权
- 隐私政策合规