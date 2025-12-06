#!/usr/bin/env bash

set -euo pipefail

# Craveva App Engine Standard (PHP) deployment script
# - Provisions/validates infra (APIs, App Engine app, IAM bindings)
# - Configures Cloud SQL DB/user and Secret Manager
# - Renders env and deploys application
# - Sets up domain mapping with managed SSL
# - Verifies application health and logs all steps
# - Idempotent: safe to rerun; skips already‑configured resources

LOG_DIR="${LOG_DIR:-./deploy-logs}"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
LOG_FILE="$LOG_DIR/deploy-$TIMESTAMP.log"
mkdir -p "$LOG_DIR"
exec > >(tee -a "$LOG_FILE") 2>&1

info() { echo "[INFO] $*"; }
warn() { echo "[WARN] $*"; }
error() { echo "[ERROR] $*"; }

usage() {
  cat <<'USAGE'
Usage: deploy_app_engine.sh [options]

Required:
  --project <id>             GCP project ID
  --region <region>          Region (e.g. asia-southeast1)
  --domain <fqdn>            App domain (e.g. hub.craveva.com)
  --sql-instance <name>      Cloud SQL instance name
  --sql-conn <conn>          Cloud SQL instance connection name
  --db-name <name>           Database name
  --db-user <name>           Database user name

Optional:
  --app-src <path>           App source path containing app.yaml (default: .)
  --db-pass-secret <name>    Secret Manager secret name for DB password (default: db-password)
  --db-password <value>      DB password value (if not set, prompt)

Examples:
  ./deploy_app_engine.sh \
    --project craveva-org-55934-project \
    --region asia-southeast1 \
    --domain hub.craveva.com \
    --sql-instance craveva-sql-db \
    --sql-conn craveva-org-55934-project:asia-southeast1:craveva-sql-db \
    --db-name laravel \
    --db-user craveva-sql-db \
    --app-src .
USAGE
}

PROJECT=""; REGION=""; DOMAIN=""; SQL_INSTANCE=""; SQL_CONN=""; DB_NAME=""; DB_USER="";
APP_SRC="."; DB_PASS_SECRET="db-password"; DB_PASSWORD_VAL=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --project) PROJECT="$2"; shift 2;;
    --region) REGION="$2"; shift 2;;
    --domain) DOMAIN="$2"; shift 2;;
    --sql-instance) SQL_INSTANCE="$2"; shift 2;;
    --sql-conn) SQL_CONN="$2"; shift 2;;
    --db-name) DB_NAME="$2"; shift 2;;
    --db-user) DB_USER="$2"; shift 2;;
    --app-src) APP_SRC="$2"; shift 2;;
    --db-pass-secret) DB_PASS_SECRET="$2"; shift 2;;
    --db-password) DB_PASSWORD_VAL="$2"; shift 2;;
    -h|--help) usage; exit 0;;
    *) error "Unknown arg: $1"; usage; exit 1;;
  esac
done

[[ -n "$PROJECT" && -n "$REGION" && -n "$DOMAIN" && -n "$SQL_INSTANCE" && -n "$SQL_CONN" && -n "$DB_NAME" && -n "$DB_USER" ]] || { error "Missing required args"; usage; exit 1; }

command -v gcloud >/dev/null 2>&1 || { error "gcloud not found"; exit 1; }

info "Setting gcloud config project=$PROJECT region=$REGION"
gcloud config set project "$PROJECT" >/dev/null
gcloud config set compute/region "$REGION" >/dev/null || true

info "Enabling required APIs (idempotent)"
gcloud services enable appengine.googleapis.com sqladmin.googleapis.com secretmanager.googleapis.com iam.googleapis.com --quiet

APP_ENGINE_SA="${PROJECT}@appspot.gserviceaccount.com"

info "Ensuring App Engine application exists in $REGION"
if ! gcloud app describe >/dev/null 2>&1; then
  gcloud app create --region="$REGION" --quiet
else
  info "App Engine application already exists"
fi

info "Granting Cloud SQL Client to App Engine default SA"
gcloud projects add-iam-policy-binding "$PROJECT" \
  --member="serviceAccount:${APP_ENGINE_SA}" \
  --role="roles/cloudsql.client" --quiet >/dev/null || true

info "Validating Cloud SQL instance: $SQL_INSTANCE"
gcloud sql instances describe "$SQL_INSTANCE" >/dev/null 2>&1 || { error "Cloud SQL instance $SQL_INSTANCE not found"; exit 1; }

info "Ensuring database exists: $DB_NAME"
if ! gcloud sql databases describe "$DB_NAME" --instance="$SQL_INSTANCE" >/dev/null 2>&1; then
  gcloud sql databases create "$DB_NAME" --instance="$SQL_INSTANCE"
else
  info "Database $DB_NAME already exists"
fi

info "Ensuring DB user exists: $DB_USER"
if ! gcloud sql users list --instance="$SQL_INSTANCE" --format="value(name)" | grep -Fx "$DB_USER" >/dev/null; then
  [[ -n "$DB_PASSWORD_VAL" ]] || {
    read -r -s -p "Enter DB password for user $DB_USER: " DB_PASSWORD_VAL; echo
  }
  gcloud sql users create "$DB_USER" --instance="$SQL_INSTANCE" --password="$DB_PASSWORD_VAL"
else
  info "DB user exists; updating password"
  if [[ -z "$DB_PASSWORD_VAL" ]]; then
    warn "No --db-password provided; skipping password update"
  else
    gcloud sql users set-password "$DB_USER" --instance="$SQL_INSTANCE" --password="$DB_PASSWORD_VAL"
  fi
fi

if [[ -n "$DB_PASSWORD_VAL" ]]; then
  info "Storing DB password in Secret Manager: $DB_PASS_SECRET (idempotent)"
  if ! gcloud secrets describe "$DB_PASS_SECRET" >/dev/null 2>&1; then
    printf "%s" "$DB_PASSWORD_VAL" | gcloud secrets create "$DB_PASS_SECRET" --data-file=- --replication-policy="automatic"
  else
    printf "%s" "$DB_PASSWORD_VAL" | gcloud secrets versions add "$DB_PASS_SECRET" --data-file=-
  fi
fi

info "Rendering app.yaml with environment variables (no secrets persisted to VCS)"
RENDERED_DIR="./rendered"
mkdir -p "$RENDERED_DIR"
APP_YAML_SRC="$APP_SRC/app.yaml"
APP_YAML_DST="$RENDERED_DIR/app.yaml"
[[ -f "$APP_YAML_SRC" ]] || { error "Missing app.yaml at $APP_YAML_SRC"; exit 1; }

DB_PASSWORD_DEPLOY="$DB_PASSWORD_VAL"
if [[ -z "$DB_PASSWORD_DEPLOY" ]]; then
  DB_PASSWORD_DEPLOY="$(gcloud secrets versions access latest --secret="$DB_PASS_SECRET" || true)"
fi
[[ -n "$DB_PASSWORD_DEPLOY" ]] || { error "DB password not available for deployment"; exit 1; }

cat > "$APP_YAML_DST" <<YAML
runtime: php81
env: standard
runtime_config:
  document_root: public
beta_settings:
  cloud_sql_instances: ${SQL_CONN}
handlers:
- url: /favicon\.ico
  static_files: public/favicon.ico
  upload: public/favicon.ico
- url: /robots\.txt
  static_files: public/robots.txt
  upload: public/robots.txt
- url: /storage
  static_dir: public/storage
- url: /assets
  static_dir: public/assets
- url: /.*
  script: auto
env_variables:
  APP_ENV: local
  APP_DEBUG: false
  APP_URL: https://${DOMAIN}
  DB_CONNECTION: mysql
  DB_HOST: localhost
  DB_PORT: 3306
  DB_DATABASE: ${DB_NAME}
  DB_USERNAME: ${DB_USER}
  DB_PASSWORD: ${DB_PASSWORD_DEPLOY}
  DB_SOCKET: /cloudsql/${SQL_CONN}
  QUEUE_CONNECTION: sync
  CACHE_DRIVER: file
  SESSION_DRIVER: file
  TELESCOPE_ENABLED: false
YAML

info "Deploying to App Engine"
gcloud app deploy "$APP_YAML_DST" --quiet

APP_URL="$(gcloud app browse --no-launch-browser 2>/dev/null | tail -n1 | awk '{print $NF}')"
info "App deployed. Default URL: $APP_URL"

info "Ensuring domain mapping for ${DOMAIN} with managed certs"
if ! gcloud app domain-mappings describe "$DOMAIN" >/dev/null 2>&1; then
  gcloud app domain-mappings create "$DOMAIN" --certificate-management="automatic" --quiet || warn "Domain mapping creation returned non-fatal issue"
else
  info "Domain mapping already exists"
fi

info "Domain mapping DNS records (add to your DNS provider)"
gcloud app domain-mappings describe "$DOMAIN" --format="json(resourceRecords)" || true

info "Verifying /login endpoint"
TARGET_URL="https://${DOMAIN}/login"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$TARGET_URL" || true)
if [[ "$HTTP_CODE" == "200" || "$HTTP_CODE" == "302" ]]; then
  info "Verification OK: /login returned HTTP $HTTP_CODE"
else
  warn "Verification returned HTTP $HTTP_CODE. Recent logs:"
  gcloud app logs read --limit=50 || true
fi

ARCHIVE="craveva-deploy-$TIMESTAMP.zip"
info "Packaging deployment artifacts: $ARCHIVE"
zip -qr "$ARCHIVE" "$APP_YAML_DST" "$LOG_FILE" "$0" || warn "zip packaging warning"

info "Done. Log: $LOG_FILE"

