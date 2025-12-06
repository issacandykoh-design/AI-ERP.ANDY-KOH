# Project Context: Craveva Hub

## Project Overview
**Craveva Hub** is a comprehensive platform built on **Laravel 10** (Modular Monolith) and **Python** (AI Services), hosted on **Google Cloud Platform (GCP)**.
The goal is to modernize the legacy system by integrating AI-driven features and managing development using the **BMad Agile Scrum** framework.

## Technology Stack
- **Backend Framework**: Laravel 10.x (PHP 8.1+)
- **AI Services**: Python scripts, utilizing **OpenRouter** as the primary API gateway for LLMs.
- **Infrastructure**: Google Cloud Platform (GCP)
  - Compute Engine (Hub Server: 34.126.124.196)
  - Cloud Run / GKE (for containerized AI services)
  - Cloud Storage & SQL
- **Architecture**: Modular structure using `nwidart/laravel-modules`. New features must be packaged as independent modules.

## Development Methodology
- **Framework**: BMad Method (Agile AI-Driven Development)
- **Agents**:
  - **Scrum Master (SM)**: Manages sprints, stories, and tasks.
  - **Architect**: Designs modular architecture and AI integration patterns.
  - **Developer**: Implements features using Laravel and Python best practices.
- **API Strategy**: All AI calls should route through OpenRouter.

## Key Objectives
1.  **Refactor Legacy Features**: Replace manual management tools with AI-driven automation.
2.  **Modular Development**: All new functions must be built as Laravel Modules.
3.  **Infrastructure Optimization**: Ensure seamless integration between Laravel core and Python AI microservices on GCP.

## Environment Configuration
- **API Provider**: OpenRouter
- **Key Env Var**: `OPENROUTER_API_KEY` (Must be set in `.env`)
