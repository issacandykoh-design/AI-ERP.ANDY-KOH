# SMTP 邮件配置指南

## 概述
本指南将帮助您配置 SMTP 邮件设置，以解决 "550 5.7.1 Relaying denied" 错误。

## 配置步骤

### 1. 编辑 .env 文件
在项目根目录的 `.env` 文件中，已经添加了以下 SMTP 配置模板：

```env
# SMTP Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. 配置不同的邮件服务商

#### Gmail 配置
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password  # 使用应用专用密码
MAIL_ENCRYPTION=tls
```

**注意**: 对于 Gmail，您需要：
1. 启用两步验证
2. 生成应用专用密码
3. 使用应用专用密码而不是账户密码

#### SMTP2GO 配置（推荐）
```env
MAIL_HOST=mail.smtp2go.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp2go-username
MAIL_PASSWORD=your-smtp2go-password
MAIL_ENCRYPTION=tls
```

#### Mailgun 配置
```env
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
```

#### SendGrid 配置
```env
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
```

### 3. 应用配置更改
配置完成后，运行以下命令使配置生效：

```bash
php artisan config:cache
```

### 4. 测试邮件功能
1. 登录管理面板
2. 进入 设置 > 邮件设置
3. 点击 "测试邮件" 按钮验证配置

## 常见问题解决

### 问题：550 5.7.1 Relaying denied
**原因**: 缺少 SMTP 认证配置
**解决方案**: 确保正确配置了 MAIL_USERNAME 和 MAIL_PASSWORD

### 问题：Gmail 认证失败
**原因**: 使用了账户密码而不是应用专用密码
**解决方案**: 
1. 启用 Gmail 两步验证
2. 生成应用专用密码
3. 使用应用专用密码替换 MAIL_PASSWORD

### 问题：连接超时
**原因**: 防火墙或网络限制
**解决方案**: 
1. 检查防火墙设置
2. 尝试不同的端口（25, 465, 587）
3. 联系网络管理员

## 安全建议

1. **不要在代码中硬编码邮件凭据**
2. **使用环境变量存储敏感信息**
3. **定期更换邮件密码**
4. **使用专用的邮件服务账户**

## 推荐的邮件服务商

1. **SMTP2GO** - 专业的 SMTP 服务，易于配置
2. **SendGrid** - 可靠的邮件发送服务
3. **Mailgun** - 开发者友好的邮件 API
4. **Gmail** - 适合小规模使用

## 支持

如果您在配置过程中遇到问题，请：
1. 检查邮件服务商的文档
2. 验证网络连接
3. 查看 Laravel 日志文件
4. 联系技术支持团队