.PHONY: init build rebuild dev prod install migrate rollback seed fresh restart stop destroy

init:
	cp -n .env.example .env || true

install:
	docker compose run --rm app composer install
	docker compose run --rm app php artisan key:generate

build:
	docker compose build

rebuild:
	docker compose build --no-cache

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
	docker compose exec app php artisan migrate:fresh --seed --force

restart:
	docker compose restart

stop:
	docker compose stop

destroy:
	@read -p "Remove containers and volumes? [y/N] " ans && ([ "$$ans" = "y" ] || [ "$$ans" = "Y" ]) && docker compose down -v || true
