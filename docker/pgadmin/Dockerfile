FROM dpage/pgadmin4:latest
# Preserve original entrypoint; our wrapper (runs as root) writes /tmp/pgpass from DB_* env then runs it
USER root
RUN cp /entrypoint.sh /entrypoint-pgadmin.sh
COPY docker/pgadmin/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
# Leave as root so entrypoint can chown /tmp/pgpass; original entrypoint drops to pgadmin for the app
