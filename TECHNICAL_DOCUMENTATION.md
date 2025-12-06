# Craveva SaaS - Technical Architecture & Specification

**Date:** 2025-12-06
**Version:** 1.0.0
**Confidentiality:** Internal Use Only

---

## 1. Executive Summary
Craveva SaaS is a comprehensive, modular Enterprise Resource Planning (ERP) and Business Management platform designed for multi-tenant (SaaS) environments. It provides a unified suite of tools for managing business operations, including Human Resources (Recruitment, Payroll, Biometrics), Financials (Invoicing, Expenses, Purchase), Project Management, and Client Relations (CRM, Tickets).

The platform is built on a robust **Laravel 10** backend with a modular architecture, ensuring scalability and maintainability. The frontend utilizes **React** and **Bootstrap 4** for a responsive and interactive user experience.

---

## 2. System Architecture

### 2.1 Technology Stack

| Component | Technology | Version | Description |
|-----------|------------|---------|-------------|
| **Backend Framework** | Laravel | 10.0.0 | Core PHP framework adhering to MVC architecture. |
| **Language** | PHP | ^8.1 | Server-side scripting language. |
| **Frontend Library** | React | ^19.2.0 | JavaScript library for building user interfaces. |
| **Frontend Framework** | Bootstrap | 4.3.1 | CSS framework for responsive design. |
| **Module Management** | nwidart/laravel-modules | 10.0.6 | Package for handling modular code structure. |
| **Database** | MySQL / MariaDB | - | Relational database management system. |
| **Cache/Queue** | Redis | - | In-memory data structure store (inferred). |
| **API Auth** | Laravel Sanctum | ^3.2 | Lightweight authentication system for SPAs and APIs. |
| **Backend Auth** | Laravel Fortify | ^1.7 | Backend agnostic authentication implementation. |
| **Billing** | Laravel Cashier | ^14.5 | Subscription billing interface (Stripe/Paddle). |

### 2.2 Modular Architecture
The application follows a Domain-Driven Design (DDD) approach using the `nwidart/laravel-modules` package. Each functional area is encapsulated in its own module, containing its specific Routes, Controllers, Models (Entities), Views, and Configuration.

**Directory Structure:**
```
/Modules
  ├── Affiliate/       # Affiliate Management
  ├── Asset/           # Asset Tracking
  ├── Biolinks/        # Social Bio Links
  ├── Biometric/       # Biometric Attendance Integration
  ├── Craveva/         # Core Craveva Features
  ├── CyberSecurity/   # Security & Compliance
  ├── EInvoice/        # Electronic Invoicing
  ├── LanguagePack/    # Localization & Translation
  ├── Letter/          # Official Letter Generation
  ├── MenuCustomization/ # Dynamic Menu Management
  ├── OCR/             # Optical Character Recognition
  ├── Payroll/         # Salary & Compensation
  ├── Performance/     # Employee Performance Management
  ├── ProjectRoadmap/  # Project Planning
  ├── Purchase/        # Procurement & Inventory
  ├── QRCode/          # QR Code Generation
  ├── Recruit/         # Applicant Tracking System (ATS)
  ├── RestAPI/         # Public & Mobile API Endpoints
  ├── ServerManager/   # Server Health & Management
  ├── Sms/             # SMS Notification Gateways
  ├── Subdomain/       # Multi-tenancy Subdomain Routing
  ├── Webhooks/        # External Integration Webhooks
  └── Zoom/            # Video Conferencing Integration
```

---

## 3. Software & Module Registry

The following table details the installed modules and their identified versions.

| Module Name | Version | Description |
|-------------|---------|-------------|
| **Affiliate** | *See module.json* | Management of affiliate programs and payouts. |
| **Asset** | *See module.json* | Tracking of physical and digital company assets. |
| **Biolinks** | *See module.json* | Link-in-bio tools for social media. |
| **Biometric** | *See module.json* | Integration with hardware biometric devices. |
| **EInvoice** | *See module.json* | Digital invoice generation and processing. |
| **OCR** | *See module.json* | Extracting text from images/documents. |
| **Payroll** | 2.1.72 | Comprehensive payroll processing engine. |
| **Recruit** | 2.2.4 | Recruitment pipeline and candidate management. |
| **Sms** | 2.1.4 | SMS gateway integrations (Twilio, etc.). |
| **RestAPI** | *See module.json* | Core API infrastructure for mobile apps. |
| **Subdomain** | *See module.json* | Handling tenant-specific subdomains (e.g., tenant.craveva.com). |

*Note: Exact versions for all modules can be found in their respective `version.txt` files.*

---

## 4. Business Logic & Core Entities

The application's business logic is divided between **Core Entities** (Global/Shared) and **Module Entities** (Feature-specific).

### 4.1 Core Entities (`app/Models`)
These models represent the fundamental data structure of the application.

*   **User & Role Management**:
    *   `User`: The central identity model.
    *   `Role`: Defines user permissions (e.g., Admin, Employee, Client).
    *   `Team`: Groups users for collaboration.
    *   `Session`: Manages active user sessions.
*   **Organizational Structure**:
    *   `Company`: Represents the tenant or client organization.
    *   `Department` / `Designation` (Inferred): Employee hierarchy.
*   **Project Management**:
    *   `Project`: Main container for work.
    *   `Task`: Individual work items.
    *   `SubTask`: Granular breakdown of tasks.
    *   `Issue`: Bug tracking or problem reports.
    *   `TimeLog`: Time tracking for tasks.
*   **CRM & Sales**:
    *   `Lead`: Potential clients.
    *   `Deal`: Sales opportunities.
    *   `Client`: Converted customers.
*   **Finance**:
    *   `Invoice`: Billing records.
    *   `Payment`: Transaction records.
    *   `Expense`: Company expenditures.
    *   `Tax`: Tax rules and rates.
    *   `Order`: Product orders.
*   **Support**:
    *   `Ticket`: Helpdesk support tickets.

### 4.2 Key Business Workflows

#### 4.2.1 Multi-Tenancy (SaaS)
The `Subdomain` module and `Company` model work together to isolate data.
*   **Logic**: Incoming requests are checked for a subdomain.
*   **Resolution**: The subdomain is mapped to a `Company` ID.
*   **Scope**: Global scopes are applied to Models to filter data by the current `Company`.

#### 4.2.2 Recruitment (ATS)
Located in `Modules/Recruit`.
*   **Flow**: Job Creation -> Application Form -> Candidate Entry -> Interview Scheduling -> Evaluation -> Offer Letter -> Onboarding.
*   **Entities**: `Job`, `Application`, `Interview`, `OfferLetter`.

#### 4.2.3 Payroll
Located in `Modules/Payroll`.
*   **Flow**: Employee Salary Structure Definition -> Monthly Attendance Sync -> Addition/Deduction Calculation -> Payslip Generation -> Payout.
*   **Entities**: `SalaryStructure`, `Payroll`, `Payslip`.

---

## 5. Panels & Interfaces

The application exposes multiple interfaces tailored to different user personas.

### 5.1 Super Admin Panel
*   **Access**: Global administrators.
*   **Features**: Tenant management, Subscription plan management, System configuration, Global reporting.
*   **Route Prefix**: `/super-admin` (Typical convention).

### 5.2 Company Admin Panel
*   **Access**: Tenant administrators.
*   **Features**: Employee management, Module configuration, Financial overview, Role assignment.

### 5.3 Employee Panel
*   **Access**: Standard staff users.
*   **Features**: Self-service portal (Leave application, Payslip view), Task management, Time tracking.

### 5.4 Client Panel
*   **Access**: External clients.
*   **Features**: Project progress view, Invoice payment, Ticket submission.

---

## 6. Infrastructure & Deployment (GCP Live)

The infrastructure has been upgraded to **Google Cloud Platform (Singapore Region: asia-southeast1)**, moving away from shared hosting to dedicated, isolated environments.

### 6.1 Compute Engine (Dedicated VMs)

| Domain | Instance Name | Specs | Disk | Public IP | Role |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **hub.craveva.com** | `craveva-hub-server` | 4 vCPU, 4 GB RAM | 200 GB | `34.126.124.196` | Central ERP Hub |
| **ai.craveva.com** | `craveva-ai` | 4 vCPU, 4 GB RAM | 100 GB | `136.110.35.154` | AI Services (Python) |
| **deerpos.com** | `craveva-deerpos` | 2 vCPU, 2 GB RAM | 100 GB | `35.198.237.131` | POS System |
| **whatsapp** | `craveva-whatsapp` | 4 vCPU, 4 GB RAM | 100 GB | `35.240.153.233` | WA Integration |
| **staging** | `craveva-staging` | 2 vCPU, 2 GB RAM | 20 GB | `35.240.158.191` | Testing Env |

### 6.2 Cloud SQL (Managed Databases)

Each domain has a **Dedicated Cloud SQL Instance** to ensure data isolation and security.

*   **Hub DB:** `craveva-hub-server` (MySQL 8.0, 100GB SSD)
*   **AI DB:** `craveva-ai-db-v2` (MySQL 8.0, 50GB SSD)
*   **POS DB:** `craveva-deerpos-db-v2` (MySQL 8.0, 50GB SSD)
*   **WA DB:** `craveva-whatsapp-db-v2` (MySQL 8.0, 50GB SSD)

### 6.3 Security & Networking
*   **Firewall:** Strict rules allowing only HTTP/HTTPS (80/443) and SSH (22) for authorized keys.
*   **SSH Access:** Password authentication disabled. Key-based access only (`.ssh/google_compute_engine`).
*   **Monitoring:** Google Cloud Ops Agent installed for real-time Memory & Disk usage tracking.

---

## 7. Cost Optimization & Future Roadmap

*   **Current Status:** All environments are stable and isolated.
*   **Optimization Opportunity:** Disk sizes for AI, POS, and WA can be reduced from 100GB to 20GB in the future to save costs (~$18/mo savings).
*   **Hub Constraint:** The Hub server requires minimum 60GB disk space due to current data volume (40GB used).

---
*End of Document Summary*
