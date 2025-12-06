# Craveva Tech Development Guide & Proposal
**Prepared for:** Craveva Tech Team (Hub.craveva.com)
**Date:** 2025-12-06
**Status:** Draft Proposal

---

## 1. Executive Summary

This document outlines the standardized development workflow for Craveva Tech. It addresses previous issues regarding broken pages, localization errors, and upgrade conflicts caused by direct file editing. By adopting this "Laravel Way" of development, the team can ensure stability, scalability, and easier future updates.

## 2. The "Golden Rules" of Laravel Development

To prevent the "broken pages" and "locale issues" you experienced, all developers must strictly adhere to these rules:

### ❌ NEVER Do This:
1.  **Never edit files in `public/`**: Files like `public/css/main.css` or `public/js/app.js` are **generated** files. Any changes you make here will be overwritten the next time the code is compiled.
2.  **Never edit files in `vendor/`**: These are external libraries managed by Composer. Updating dependencies will wipe your changes.
3.  **Never hardcode text**: Don't write `<h1>Welcome</h1>`. This breaks multi-language support.
4.  **Never write logic in Views**: Don't put complex PHP database queries inside `.blade.php` files.

### ✅ ALWAYS Do This:
1.  **Edit Source Files**: Modify `resources/scss/` for styles and `resources/js/` for scripts.
2.  **Use Localization**: Write `<h1>@lang('app.welcome')</h1>` and define the text in `resources/lang/en/app.php`.
3.  **Compile Assets**: Run `npm run dev` or `npm run watch` to update the `public/` folder safely.
4.  **Use Controllers**: Fetch data in a Controller (`app/Http/Controllers`) and pass it to the View.

---

## 3. How to Modify UI/UX (The Correct Way)

Your project uses **Laravel Mix** to bundle assets. Here is the workflow to change the design:

### Step 1: Locate the Source
*   **Global CSS**: `resources/scss/main.scss` (and imported files in `resources/scss/includes/`).
*   **Global JS**: `resources/js/main.js` or `resources/js/custom.js`.
*   **Module Views**: `Modules/{ModuleName}/Resources/views/`.
*   **Global Views**: `resources/views/`.

### Step 2: Make the Change
**Example: Changing the color of the sidebar.**
Instead of editing `public/css/main.css`, you should:
1.  Open `resources/scss/sidebar.scss`.
2.  Change the variable or style rule.

**Example: Changing a button text in the Payroll Module.**
1.  Open `Modules/Payroll/Resources/views/index.blade.php`.
2.  Find the text. **Do not** just type "New Salary".
3.  Change it to `@lang('payroll::modules.payroll.newSalary')`.
4.  Add `'newSalary' => 'New Salary',` to `Modules/Payroll/Resources/lang/en/modules.php`.

### Step 3: Compile Changes
You need to turn your SCSS/JS into the final CSS/JS browser-readable files.
Run this command in your terminal:
```bash
npm run dev
```
*Or, to watch for changes automatically while you work:*
```bash
npm run watch
```

---

## 4. Updating Existing Features (Modules)

Since your app uses `nwidart/laravel-modules`, each feature is a mini-app.

**Scenario: Adding a new field "Nick Name" to the Employee Profile.**

1.  **Database**: Create a migration.
    ```bash
    php artisan module:make-migration AddNickNameField Employees
    ```
2.  **Model**: Update `Modules/Employees/Entities/Employee.php` to include `nick_name` in `$fillable`.
3.  **View**: Edit `Modules/Employees/Resources/views/create.blade.php` to add the input field.
    ```html
    <div class="form-group">
        <label>@lang('app.nickName')</label>
        <input type="text" name="nick_name" class="form-control">
    </div>
    ```
4.  **Controller**: Update `Modules/Employees/Http/Controllers/EmployeesController.php` to save the data.

---

## 5. Craveva Tech Workspace Setup (Trae AI Solo Mode)

For your team to work effectively using Trae AI, follow this setup on each developer's machine:

### Prerequisites
1.  **XAMPP / Laragon**: For PHP (8.1+) and MySQL.
2.  **Node.js (LTS Version)**: For compiling assets.
3.  **Git**: For version control.
4.  **Composer**: Dependency manager for PHP.

### Setup Checklist
1.  **Clone the Repo**: `git clone <your-repo-url>`
2.  **Install PHP Dependencies**:
    ```bash
    composer install
    ```
3.  **Install JS Dependencies**:
    ```bash
    npm install
    ```
4.  **Environment Setup**:
    *   Copy `.env.example` to `.env`.
    *   Set `APP_URL=http://localhost` (or your local domain).
    *   Set Database credentials.
    *   Run `php artisan key:generate`.
5.  **Compile Assets**:
    ```bash
    npm run dev
    ```
6.  **Run Migrations**:
    ```bash
    php artisan migrate
    ```

### Using Trae AI effectively
Since you are not "coders", use Trae as your pair programmer. Give it instructions in **Tasks**, not just code snippets.

**Good Prompt for Trae:**
> "I need to add a 'Export to PDF' button on the Invoices page. Please find the Invoice Controller, create a function to generate the PDF using dompdf, add the route in web.php, and place the button in the index.blade.php view. Ensure you use localization for the button label."

**Bad Prompt:**
> "Add button here." (Trae might hardcode it, breaking your rules).

---

## 6. BMad Agile AI Integration (New Setup)

We have integrated the **BMad Method (Agile AI-Driven Development)** to modernize the workflow. This setup uses AI agents to enforce the "Golden Rules" and manage the project.

### The AI Agents
We have configured three specialized agents to work with you:

1.  **@Architect (Chief Architect)**
    *   **Role**: Enforces the Modular Monolith architecture.
    *   **Responsibility**: Ensures strict separation between Laravel Core and Python AI Services and validates new module designs.
    *   **Use for**: Planning new features, database schema design, and high-level technical decisions.

2.  **@Developer (Full Stack Expert)**
    *   **Role**: Laravel 10 & Python Expert.
    *   **Responsibility**: Writes PSR-12 compliant PHP and robust Python scripts.
    *   **Use for**: Writing code, fixing bugs, and implementing features defined by the Architect.
    *   **Memory**: Knows to use `OpenRouter` for all AI integrations.

3.  **@ScrumMaster (Agile Coach)**
    *   **Role**: Process Guardian.
    *   **Responsibility**: Prevents "vibe coding" by ensuring every task has a clear User Story and Acceptance Criteria.
    *   **Use for**: Sprint planning, backlog management, and keeping the team on track.

### AI Services Infrastructure
*   **API Gateway**: OpenRouter (configured in `.env`).
*   **Python Integration**: Core AI logic resides in `AI Enterprise Business/`.
*   **Workflow**:
    1.  **Plan**: Chat with `@ScrumMaster` to define the User Story.
    2.  **Design**: Ask `@Architect` to outline the Module structure.
    3.  **Build**: Task `@Developer` to write the code.

---

## 7. Proposal for Next Steps

To get Craveva Tech running smoothly, I recommend we perform a **"Health Check & Standardization"** sprint:

1.  **Audit**: I will scan the `public/` folder to see if any custom code was "hacked" in there and move it to `resources/js/custom.js` or `resources/scss/custom.scss`.
2.  **Fix Localization**: Identify hardcoded English strings in your Blade files and move them to language files.
3.  **Documentation**: Create a `DEVELOPER_README.md` specific to your project with these exact commands so your team never forgets.

Shall we proceed with step 1 (The Audit)?
