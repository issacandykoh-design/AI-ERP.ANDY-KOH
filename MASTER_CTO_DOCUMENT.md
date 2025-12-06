# Craveva SaaS - Master CTO Technical Documentation

**Date:** 2025-12-06
**Version:** 2.0.0 (Consolidated Master)
**Confidentiality:** Internal Use Only (CTO & Tech Lead)
**Status:** Live Production

---

## 1. Executive Summary

Craveva SaaS is a comprehensive, modular Enterprise Resource Planning (ERP) and Business Management platform designed for multi-tenant environments. It unifies Human Resources, Financials, Project Management, and Client Relations into a single ecosystem.

This document serves as the **Single Source of Truth** for Craveva's technical architecture, cloud infrastructure on Google Cloud Platform (GCP), and development standards. It consolidates previous technical guides and infrastructure records into one master directive.

The platform runs on a dedicated **Google Cloud Platform** environment in the **asia-southeast1 (Singapore)** region, utilizing Compute Engine for application logic and Cloud SQL for data persistence, ensuring high availability and security.

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
| **Database** | MySQL | 8.0 | Cloud SQL for MySQL. |
| **API Auth** | Laravel Sanctum | ^3.2 | Authentication for SPAs and APIs. |

### 2.2 Modular Architecture (Domain-Driven)

The application uses `nwidart/laravel-modules` to encapsulate functional areas.
**Key Modules:**
*   `Craveva`: Core features.
*   `Recruit`: Applicant Tracking System (ATS).
*   `Payroll`: Salary & Compensation.
*   `Subdomain`: Multi-tenancy routing logic.
*   `RestAPI`: Mobile & External API endpoints.

---

## 3. GCP Infrastructure (Live Status)

**Project ID:** `craveva-org-55934-project`
**Region:** `asia-southeast1` (Singapore)
**Zone:** `asia-southeast1-a`

### 3.1 Compute Engine Instances (VMs)

| Domain / Role | Instance Name | Machine Type | Public IP | Internal IP | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Hub (Central ERP)** | `craveva-hub-server` | `e2-highcpu-4` (4 vCPU) | `34.126.124.196` | `10.1.0.5` | **RUNNING** |
| **AI Services** | `craveva-ai` | `e2-highcpu-4` (4 vCPU) | `136.110.35.154` | `10.148.0.7` | **RUNNING** |
| **WhatsApp Integration** | `craveva-whatsapp` | `e2-highcpu-4` (4 vCPU) | `35.240.153.233` | `10.148.0.9` | **RUNNING** |
| **POS System** | `craveva-deerpos` | `e2-highcpu-2` (2 vCPU) | `35.198.237.131` | `10.148.0.8` | **RUNNING** |
| **Staging / Testing** | `craveva-staging` | `e2-highcpu-2` (2 vCPU) | `35.240.158.191` | `10.148.0.11` | **RUNNING** |

### 3.2 Cloud SQL Instances (Databases)

| Instance Name | Related VM | Version | Region | Status |
| :--- | :--- | :--- | :--- | :--- |
| `craveva-hub-server` | Hub | MySQL 8.0 | asia-southeast1 | **RUNNABLE** |
| `craveva-ai-db` | AI | MySQL 8.0 | asia-southeast1 | **RUNNABLE** |
| `craveva-whatsapp-db` | WhatsApp | MySQL 8.0 | asia-southeast1 | **RUNNABLE** |
| `craveva-deerpos-db` | POS | MySQL 8.0 | asia-southeast1 | **RUNNABLE** |
| `craveva-staging-db` | Staging | MySQL 8.0 | asia-southeast1 | **RUNNABLE** |
| *craveva-ai-db-v2* | *Legacy/Backup* | MySQL 8.0 | asia-southeast1 | **RUNNABLE** |
| *craveva-whatsapp-db-v2* | *Legacy/Backup* | MySQL 8.0 | asia-southeast1 | **RUNNABLE** |
| *craveva-deerpos-db-v2* | *Legacy/Backup* | MySQL 8.0 | asia-southeast1 | **RUNNABLE** |

---

## 4. Security & Access Control

### 4.1 SSH Access (Project-Wide)
**User:** `issac`
**Key Path:** `C:\Users\issac\.ssh\google_compute_engine`
**Public Key Fingerprint:** `ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIMnN13iUEFiqx9H5v7SOVMPIkmNNs2oWSwo46aF4aELW`

### 4.2 Database Access Credentials
*   **Hub Database:**
    *   User: `hubcraveva`
    *   Password: `H8b$Cr4v3v4!25`
*   **AI Database:**
    *   User: `aicraveva`
    *   Password: `Ai$Cr4v3v4!25`
*   **WhatsApp Database:**
    *   User: `whatsappcraveva`
    *   Password: `Wh4t$Cr4v3v4!25`
*   **POS Database:**
    *   User: `deerposcraveva`
    *   Password: `D33r$Cr4v3v4!25`
*   **Staging Database:**
    *   User: `stagingcraveva`
    *   Password: `St4g$Cr4v3v4!25`

---

## 5. Development Workflow ("The Laravel Way")

To maintain stability and support localization, all developers must adhere to these rules:

### 5.1 The Golden Rules
1.  **NO Direct Edits in `public/`**: Never edit CSS/JS in `public/`. These are generated files.
2.  **NO Hardcoding**: Always use localization (e.g., `@lang('app.welcome')`) instead of hardcoded text.
3.  **NO Logic in Views**: Keep business logic in Controllers or Services.

### 5.2 Asset Compilation
We use **Laravel Mix** to compile assets.
*   **Source**: `resources/scss/` and `resources/js/`
*   **Output**: `public/css/` and `public/js/`

**Commands:**
```bash
npm run dev     # Compile for development
npm run watch   # Watch for changes
npm run prod    # Compile for production (Minified)
```

### 5.3 Workspace Setup
1.  **Clone Repo**: `git clone <repo-url>`
2.  **Install Dependencies**: `composer install` && `npm install`
3.  **Environment**: Copy `.env.example` to `.env` and configure DB.
4.  **Migrate**: `php artisan migrate`
5.  **Compile**: `npm run dev`

---

## 6. Cost Optimization Strategy

### 6.1 Resource Usage (Current)
*   **Compute**: High-CPU instances are currently provisioned. Monitoring shows varying utilization.
*   **Storage**: Disk sizes vary. Downsizing requires disk recreation (cannot shrink in-place).
*   **Databases**: Multiple dedicated instances increase cost.

### 6.2 Roadmap
1.  **Consolidation**: Consider merging low-traffic databases into a single Cloud SQL instance with separate schemas to save on instance costs.
2.  **Right-Sizing**: Analyze "Ops Agent" reports to downsize over-provisioned VMs (e.g., moving from `e2-highcpu-4` to `e2-medium` if load permits).
3.  **Commitment**: Purchase Committed Use Discounts (CUD) for baseline resources (Hub Server) for 1-3 years to reduce costs by ~30-50%.
