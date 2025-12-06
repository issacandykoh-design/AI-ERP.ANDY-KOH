# Sprint 01: AI Transition & Foundation
**Goal:** Establish the BMad Agile framework and modernize the first legacy feature using AI.
**Duration:** 2 Weeks
**Scrum Master:** @ScrumMaster (AI)

## 1. Infrastructure Setup (Priority: High)
- [x] **[Task]** Set up OpenRouter API Gateway.
  - *Status:* Completed (Key added to .env).
- [ ] **[Task]** Verify Production Deployment Pipeline.
  - *Assignee:* @Developer
  - *Acceptance Criteria:* `deploy_fix_v2.sh` successfully deploys to `craveva-hub-server`.

## 2. User Story: "Smart Dashboard" (Priority: High)
> **As a** Site Admin,
> **I want** to see an AI-generated summary of daily business metrics,
> **So that** I don't have to manually compile Excel reports.

### Acceptance Criteria
1.  **Backend:** Create a new Laravel Module `Modules/SmartDashboard`.
2.  **AI Service:** Implement `SimpleCoffeeAnalyzer` logic in `AI Enterprise Business` to process SQL data.
3.  **Frontend:** Display "Daily Insights" card on the admin dashboard.
4.  **Validation:** System must generate a 1-paragraph summary from the last 24h of sales data.

### Technical Tasks
- [ ] **@Architect:** Define the JSON schema for data exchange between Laravel and Python.
- [ ] **@Developer:** Create `SmartDashboard` module structure.
- [ ] **@Developer:** Connect `main.py` to the live database (read-only access).

## 3. Refactoring: "Legacy Code Cleanup" (Priority: Medium)
- [ ] **[Task]** Identify hardcoded "vibe coding" scripts in `html/` backup and map them to new Modules.
- [ ] **[Task]** Remove unused assets from `public/` to reduce build size.

## Action Items
1.  Run `start_bmad_dev.bat` to start the local environment.
2.  Type `@Architect help` in the chat to begin the module design.
