## Goals
- Enforce a single, consistent naming scheme across domains, GCP resources, GitHub repos, CI/CD and credentials
- Make names readable, unique, and policy-compliant (GCP: lowercase, digits, hyphens)
- Support “1 project per subdomain/domain” while keeping room for growth

## Core Rules
- Use canonical domain slug as the base: `hub.craveva.com → hub`, `ai.craveva.com → ai`, `whatsapp.craveva.com → whatsapp`, `deerpos.craveva.com → deerpos`, `staging.craveva.com → staging`
- All names are lowercase, hyphen-delimited, no spaces
- Apply a uniform prefix per resource type and reuse the domain slug everywhere
- Labels: `domain=<slug>`, `env=production|staging|development`, `component=vm|sql|lb|gcs|api`

## GCP Projects (1 Project per Subdomain/Domain)
- Project ID pattern: `craveva-<slug>`
  - Examples: `craveva-hub`, `craveva-ai`, `craveva-whatsapp`, `craveva-deerpos`, `craveva-staging`
- Project Name: `Craveva - <Slug>` (for console readability)
- Environments inside the project are differentiated via resource names and labels (`env=production|staging|development`) 

## Resource Naming Patterns
- Compute Engine VM: `vm-<slug>-<env>`
- Cloud SQL instance: `sql-<slug>-<env>`; Database: `craveva_<slug>`; User: `<slug>craveva`
- VPC: `vpc-<slug>`; Subnet: `subnet-<region>-<slug>`
- Load Balancer: `lb-<slug>-<env>`; Backend service: `be-<slug>-<env>`
- Cloud Storage Bucket: `craveva-<slug>-<env>-<region>-uploads`
- Service Account: `sa-<slug>-<env>@<project>.iam.gserviceaccount.com`
- Pub/Sub Topic: `pubsub-<slug>-<env>-events`
- Cloud Run/Functions: `svc-<slug>-<env>-api`
- Secret Manager: `craveva/<slug>/<ENV>/<SECRET_NAME>` (folder-style naming)
- KMS Keyring/Key: `kms-<slug>-keyring`, `kms-<slug>-<env>`

## DNS, SSL & Certificates
- DNS Zones: `craveva-com` (parent) with A/AAAA/CNAME for each subdomain
- Managed Certs: `cert-<slug>`; LB uses `cert-<slug>` bound to `*.craveva.com` or exact host

## GitHub Repository Naming
- Primary monorepo: `craveva-platform` (Laravel hub + modules + CI/CD)
- Optional microservices: `craveva-<slug>-service` (e.g., `craveva-ai-service`) only if needed
- CI/CD workflow files: `.github/workflows/deploy-<slug>-<env>.yml`
- Branching: `main` (production), `staging`, `develop`; domain-specific deployment uses the same branches but targets respective projects via env vars/secrets

## Environment & Configuration
- `.env` keys prefix: `HUB_`, `AI_`, `WHATSAPP_`, `DEERPOS_`, `STAGING_` when multi-tenant in one app; otherwise per-repo plain names
- `APP_URL`: `https://<slug>.craveva.com`
- Cloud SQL creds: `DB_DATABASE=craveva_<slug>`, `DB_USERNAME=<slug>craveva`
- Secret Manager path: `craveva/<slug>/<ENV>/DB_PASSWORD`

## Labels & Tagging (Cost/Usage)
- Required labels: `domain=<slug>`, `env=<env>`, `owner=tech`, `tier=app|data|edge`
- Apply to VMs, disks, SQL, buckets, LB, and service accounts

## IAM & Access
- Principle of least privilege per domain project
- Admin SA: `sa-<slug>-admin`; App SA: `sa-<slug>-app` with minimal roles (SQL Client, Storage Object Viewer, Logging Writer)

## Monitoring & Logging
- Dashboards: `craveva-<slug>-overview`
- Metrics filters use labels `domain` and `env`

## Concrete Mapping (Current Domains)
- hub.craveva.com → slug `hub`
  - Project: `craveva-hub`
  - VM: `vm-hub-production`; SQL: `sql-hub-production`; DB: `craveva_hub`; User: `hubcraveva`
- ai.craveva.com → slug `ai`
  - Project: `craveva-ai`; VM: `vm-ai-production`; SQL: `sql-ai-production`; DB: `craveva_ai`; User: `aicraveva`
- whatsapp.craveva.com → slug `whatsapp`
  - Project: `craveva-whatsapp`; VM: `vm-whatsapp-production`; SQL/DB/User follow pattern
- deerpos.craveva.com → slug `deerpos`
  - Project: `craveva-deerpos`; VM `vm-deerpos-production`; SQL/DB/User follow pattern
- staging.craveva.com → slug `staging`
  - Project: `craveva-staging`; VM `vm-staging-staging`; SQL/DB/User follow pattern

## Migration Notes
- We will align existing resource names by adding labels immediately and renaming/creating new resources only when safe (e.g., next maintenance window). DNS and certs remain unchanged.

## Request for Confirmation
- Approve this naming scheme (projects, resources, repos, labels)?
- If yes, I will: 1) configure GitHub repo names and CI/CD workflow files using this pattern; 2) apply labels across GCP resources; 3) prepare a stepwise rename/migrate plan for any non-compliant resources.