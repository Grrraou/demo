.PHONY: init build rebuild dev prod install migrate rollback seed fresh backup pgmyadmin restart stop destroy jitsi jitsi-stop jitsi-restart jitsi-logs jitsi-reset

init:
	cp -n .env.example .env || true

install:
	docker compose run --rm app composer install
	docker compose run --rm app php artisan key:generate

build:
	docker compose build
	docker compose run --rm app composer install

rebuild:
	docker compose build --no-cache
	docker compose run --rm app composer install

dev:
	docker compose up -d

prod:
	docker compose -f docker-compose.prod.yml up -d

migrate:
	docker compose exec app php artisan migrate --force

rollback:
	docker compose exec app php artisan migrate:rollback

seed:
	docker compose exec app php artisan db:seed --force

fresh:
	@echo "WARNING: This will DROP all tables and re-run migrations + seed. All data will be lost."
	@read -p "Continue? [y/N] " ans && ([ "$$ans" = "y" ] || [ "$$ans" = "Y" ]) && docker compose exec app php artisan migrate:fresh --seed --force || true

backup:
	@f=backup_$$(date +%Y%m%d_%H%M%S).sql && docker compose exec -T postgres sh -c 'pg_dump -U "$$POSTGRES_USER" "$$POSTGRES_DB"' > $$f && echo "Backup written to $$f"

pgmyadmin:
	@bash scripts/generate-pgadmin-servers.sh
	docker compose --profile pgadmin up -d --force-recreate postgres pgadmin
	@port=$$(grep '^PGADMIN_PORT=' .env 2>/dev/null | cut -d= -f2 || echo 5050); \
	echo "pgAdmin: http://127.0.0.1:$$port"; \
	echo "Server '$$(grep '^APP_NAME=' .env 2>/dev/null | cut -d= -f2 || echo ERP)' pre-configured from .env (DB_*)"

pgmyadmin-logs:
	docker compose --profile pgadmin logs -f pgadmin

restart:
	docker compose restart

stop:
	docker compose stop

destroy:
	@read -p "Remove containers and volumes? [y/N] " ans && ([ "$$ans" = "y" ] || [ "$$ans" = "Y" ]) && docker compose down -v || true

# Jitsi Meet - Video conferencing
jitsi:
	docker compose up -d prosody jicofo jvb jitsi-web
	@echo "Jitsi Meet available at http://localhost:$$(grep '^JITSI_PORT=' .env 2>/dev/null | cut -d= -f2 || echo 8443)"

jitsi-stop:
	docker compose stop prosody jicofo jvb jitsi-web

jitsi-restart:
	docker compose restart prosody jicofo jvb jitsi-web

jitsi-logs:
	docker compose logs -f prosody jicofo jvb jitsi-web

jitsi-reset:
	@read -p "Reset Jitsi config? This will remove all Jitsi settings. [y/N] " ans && \
	([ "$$ans" = "y" ] || [ "$$ans" = "Y" ]) && \
	docker compose stop prosody jicofo jvb jitsi-web && \
	docker compose rm -f prosody jicofo jvb jitsi-web && \
	rm -rf docker/jitsi/prosody/config docker/jitsi/jicofo docker/jitsi/jvb docker/jitsi/web/.jitsi-meet-cfg 2>/dev/null; \
	mkdir -p docker/jitsi/web docker/jitsi/prosody/config docker/jitsi/prosody/prosody-plugins-custom docker/jitsi/jicofo docker/jitsi/jvb docker/jitsi/transcripts && \
	docker compose up -d prosody jicofo jvb jitsi-web && \
	echo "Jitsi reset complete" || true
