# Craveva GitHub Strategy & Implementation Plan

**Date:** 2025-12-06
**Status:** Implementation Ready
**Repository Type:** Monorepo (Unified Platform)

---

## 1. Repository Structure (Monorepo)

Given that your Laravel application (`Hub`) and Python AI Services (`AI Enterprise Business`) reside in the same directory structure and are tightly integrated, we will use a **Monorepo** strategy.

**Repository Name:** `craveva-platform`

### Directory Layout
```
craveva-platform/
├── app/                 # Laravel Core (Hub)
├── Modules/             # Feature Modules (Payroll, Recruit, etc.)
├── AI Enterprise Business/ # Python AI Microservice
├── public/              # Web Entry Point
├── database/            # Migrations & Seeds
└── ...
```

### Why Monorepo?
1.  **Unified Versioning**: Keeps the Backend (Laravel) and AI Service (Python) in sync.
2.  **Simplified Deployment**: One pipeline deploys the entire platform to GCP.
3.  **Atomic Commits**: You can change the API in Laravel and the AI script in Python in a single commit.

---

## 2. Branching Strategy (GitFlow)

We will use a simplified GitFlow to manage your "few domains" and environments.

| Branch | Environment | Purpose | Protected? |
| :--- | :--- | :--- | :--- |
| `main` | **Production** | The live code running on `hub.craveva.com` and `ai.craveva.com`. | ✅ YES |
| `staging` | **Staging** | Testing ground (`staging.craveva.com`) for client review. | ❌ NO |
| `develop` | **Development** | Where all new features are merged first. | ❌ NO |

### Workflow
1.  **New Feature**: Create branch `feature/add-payroll-report` from `develop`.
2.  **Completion**: Merge `feature/...` into `develop`.
3.  **Release Candidate**: Merge `develop` into `staging` for testing.
4.  **Production Release**: Merge `staging` into `main`.

---

## 3. Ignore Rules (.gitignore)

We must prevent large files, secrets, and generated artifacts from polluting the repository.

**Global Ignores:**
*   `.env` (Secrets - **NEVER COMMIT**)
*   `/vendor` (PHP Dependencies)
*   `/node_modules` (JS Dependencies)
*   `*.log` (Logs)

**AI Service Ignores:**
*   `__pycache__/` (Compiled Python)
*   `*.bin` (Vector Database Files)
*   `.venv/` (Python Virtual Environment)
*   `*.pkl` (Pickle Files)

---

## 4. Execution Plan (Step-by-Step)

### Step 1: Configure Git & Ignore Rules
*   We will update `.gitignore` to exclude AI data files.
*   We will initialize the repository.

### Step 2: Commit Code
*   Stage all safe files.
*   Create the first "Initial Commit".

### Step 3: Create Remote Repository
*   Use GitHub CLI (`gh`) to create the private repo `craveva-platform` on GitHub.
*   Push the `main` branch.

### Step 4: Setup Branches
*   Create `staging` and `develop` branches.
*   Push them to GitHub.

---

## 5. CI/CD Pipeline (Future)

Once the repo is live, we will set up **GitHub Actions** to auto-deploy:
*   **Push to `main`** -> Triggers deployment to GCP `asia-southeast1` (Production).
*   **Push to `staging`** -> Triggers deployment to Staging VM.
