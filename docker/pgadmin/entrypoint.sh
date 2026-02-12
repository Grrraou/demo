#!/bin/bash
# Write pgpass from DB_* env (so libpq uses it; file must be 600 and owned by pgadmin user)
# Use * for host so it matches when connection is by hostname (postgres) or resolved IP (172.x.x.x)
# Per PostgreSQL docs: colons and backslashes in password must be escaped with backslash
if [[ -n "$DB_USERNAME" && -n "$DB_PASSWORD" ]]; then
  pw="${DB_PASSWORD//\\/\\\\}"
  pw="${pw//:/\\:}"
  printf '*:%s:%s:%s:%s\n' \
    "${DB_PORT:-5432}" \
    "${DB_DATABASE:-erp}" \
    "$DB_USERNAME" \
    "$pw" \
    > /tmp/pgpass
  chown 5050:5050 /tmp/pgpass
  chmod 600 /tmp/pgpass
fi
exec /entrypoint-pgadmin.sh "$@"
