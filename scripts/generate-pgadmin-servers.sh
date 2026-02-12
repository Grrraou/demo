#!/usr/bin/env bash
# Generate pgAdmin servers.json from .env (DB_* vars).
# Password is injected in-container from DB_PASSWORD env via entrypoint.
# Run from project root. Creates docker/pgadmin/servers.json.

set -e
cd "$(dirname "$0")/.."

ENV_FILE="${1:-.env}"
if [[ ! -f "$ENV_FILE" ]]; then
  echo "No $ENV_FILE found. Copy .env.example to .env and run again." >&2
  exit 1
fi

# Read DB_* and APP_NAME from .env
while IFS= read -r line; do
  if [[ "$line" =~ ^DB_HOST= ]]; then DB_HOST=$(echo "$line" | cut -d= -f2- | tr -d '"'\'''); fi
  if [[ "$line" =~ ^DB_PORT= ]]; then DB_PORT=$(echo "$line" | cut -d= -f2- | tr -d '"'\'''); fi
  if [[ "$line" =~ ^DB_DATABASE= ]]; then DB_DATABASE=$(echo "$line" | cut -d= -f2- | tr -d '"'\'''); fi
  if [[ "$line" =~ ^DB_USERNAME= ]]; then DB_USERNAME=$(echo "$line" | cut -d= -f2- | tr -d '"'\'''); fi
  if [[ "$line" =~ ^DB_PASSWORD= ]]; then DB_PASSWORD=$(echo "$line" | cut -d= -f2- | tr -d '"'\'''); fi
  if [[ "$line" =~ ^APP_NAME= ]]; then APP_NAME=$(echo "$line" | cut -d= -f2- | tr -d '"'\'''); fi
done < <(grep -E '^(DB_|APP_NAME=)' "$ENV_FILE" 2>/dev/null || true)

DB_HOST="${DB_HOST:-postgres}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE:-erp}"
DB_USERNAME="${DB_USERNAME:-erp}"
DB_PASSWORD="${DB_PASSWORD:-secret}"
APP_NAME="${APP_NAME:-ERP}"

mkdir -p docker/pgadmin

# servers.json: password comes from PGPASSFILE=/tmp/pgpass (set in container, filled by entrypoint)
cat > docker/pgadmin/servers.json << EOF
{
  "Servers": {
    "1": {
      "Name": "${APP_NAME}",
      "Group": "Servers",
      "Host": "${DB_HOST}",
      "Port": ${DB_PORT},
      "MaintenanceDB": "${DB_DATABASE}",
      "Username": "${DB_USERNAME}",
      "PassFile": "/tmp/pgpass",
      "SSLMode": "prefer"
    }
  }
}
EOF

echo "Generated docker/pgadmin/servers.json from $ENV_FILE"
