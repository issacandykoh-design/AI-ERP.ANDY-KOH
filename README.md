# AI ERP Platform

**AI-Powered Enterprise Resource Planning Platform** - A full-featured business management suite built with Laravel, integrating AI automation, CRM, HRM, Finance, Project Management, and Support Ticketing into a single unified platform.

---

## Key Features

### Core Modules

| Module | Capabilities |
|---|---|
| **CRM & Sales** | Leads, Deals, Contacts, Companies, Pipelines, Proposals |
| **HR & Payroll** | Employees, Attendance, Leaves, Holidays, Awards, Payroll, Teams |
| **Project Management** | Projects, Tasks, Sub-tasks, Tags, Milestones, Time Tracking, Kanban |
| **Finance & Invoicing** | Invoices, Payments, Expenses, Taxes, Multi-currency, Recurring |
| **Support Desk** | Tickets, Knowledge Base, SLA, Ticket Tags, Priorities |
| **Inventory** | Products, Orders, Stock, Suppliers, Categories |
| **AI Automation** | OCR processing, AI assistants, automated data extraction, smart workflows |
| **Communications** | Email (IMAP/SMTP), SMS, Push Notifications, Telegram, Slack, Zoom |
| **Globalization** | 95+ languages, Multi-currency, 200+ country flags, RTL support |

### 15+ Payment Gateways

Stripe (Cashier) - PayPal - Mollie - Razorpay - Paystack - Square - Flutterwave - Authorize.Net - PayFast - and more.

### Enterprise Integrations

- **Cloud Storage**: AWS S3, MinIO, Wasabi
- **Accounting**: QuickBooks Online
- **Communication**: Zoom, Google Workspace, Pusher Realtime
- **Error Tracking**: Sentry
- **Authentication**: Social Login (Google, Facebook, Twitter + 10+ providers)
- **DevOps**: Backups (Spatie), Log Viewer, Telescope Debugger, PHPStan

---

## Technology Stack

| Layer | Technologies |
|---|---|
| **Backend** | PHP 8.2, Laravel 10, Sanctum Auth, Fortify, REST API |
| **AI & Data** | Python, OCR, Natural Language Processing |
| **Frontend** | Blade Templates, Vue/React Components, Vite, Bootstrap, SCSS |
| **Database** | MySQL / MariaDB, Redis Cache, Eloquent ORM, Datatables |
| **Payments** | Stripe Cashier, PayPal SDK, Mollie, Razorpay, Paystack, Square |
| **DevOps** | Docker-ready, Queue Workers, Task Scheduler, PHPUnit, Playwright E2E |
| **Architecture** | Modular (nwidart/laravel-modules), HMVC, Role-based ACL (Entrust) |

---

## System Requirements

- PHP >= 8.2
- MySQL / MariaDB >= 10.5
- Composer >= 2.x
- Node.js >= 18 (for asset builds)
- Python >= 3.9 (for AI modules)
- PHP Extensions: bcmath, ctype, curl, dom, fileinfo, gd, json, mbstring, openssl, pdo_mysql, tokenizer, xml, zip, imagick

---

## Quick Start

```bash
# 1. Clone the repository
git clone https://github.com/issacandykoh-design/AI-ERP.ANDY-KOH.git
cd AI-ERP.ANDY-KOH

# 2. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 3. Configure environment
cp .env.example .env
# Edit .env with your DB, Mail, Payment, and AI credentials

# 4. Generate app key
php artisan key:generate

# 5. Run migrations & seeders
php artisan migrate --force

# 6. Build frontend assets (if needed)
npm install && npm run build

# 7. Set permissions
chmod -R 775 storage bootstrap/cache

# 8. Serve
php artisan serve
```

The application will be available at `http://localhost:8000`

---

## Documentation

- [Installation Guide](#quick-start)
- [Module Architecture](./Modules/)
- [API Routes](./routes/api.php)
- [Database Models](./app/Models/)
- [Security Policy](./SECURITY.md)
- [Contributing](#contributing)

---

## Project Structure

```
AI-ERP.ANDY-KOH/
├── app/
│   ├── Models/           # 30+ Domain models (User, Invoice, Project, Ticket, etc.)
│   ├── Http/             # Controllers & Middleware
│   ├── Helper/           # Utility functions
│   └── Console/          # Artisan commands & Scheduler
├── Modules/              # HMVC feature modules (OCR, SMS, +custom modules)
├── packages/             # Composer package symlinks
│   ├── open-rest-api/    # REST API package
│   └── craveva/          # Core platform package
├── public/
│   ├── ai/               # AI service web entry
│   ├── i18n/             # 95+ language JSON files
│   └── flags/            # Country flag assets (1x1 and 4x3)
├── routes/               # Web, API, Public, Console, Channels
├── resources/            # Blade views, JS, SCSS
├── config/               # 40+ service config files
├── database/             # Migrations, Seeders, Factories
├── tests/                # PHPUnit & Feature tests
└── tools/                # Utility scripts
```

---

## Screenshots

Preview assets are available under `public/front/` and `public/img/`.

---

## Roadmap

- [x] Core ERP Modules (CRM, HR, Finance, Projects, Support)
- [x] 95+ Language Translations
- [x] OCR & AI Document Processing Module
- [x] 15+ Payment Gateways
- [ ] Advanced AI Analytics Dashboard
- [ ] Mobile Application (React Native)
- [ ] Public Marketplace for Modules
- [ ] GraphQL API Layer
- [ ] Multi-tenant SaaS Enablement

---

## Contributing

Contributions, issues, and feature requests are welcome.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## Security

For security vulnerabilities, please review our [Security Policy](./SECURITY.md) and contact the maintainers privately. Do not open public issues for security concerns.

---

## License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

---

## Maintainers

- **Issac Andy Koh** - [issacandykoh-design](https://github.com/issacandykoh-design)
- Contact: esports3asia@gmail.com

---

<p align="center">
  <strong>Built with Laravel - The PHP Framework for Web Artisans</strong>
</p>
