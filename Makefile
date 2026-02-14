.PHONY: init build rebuild dev prod install migrate rollback seed fresh backup pgmyadmin restart stop destroy

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
