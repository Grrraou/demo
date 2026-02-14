# ERP – Laravel Backend

Production-ready ERP backend: Laravel 11, PHP 8.2+, PostgreSQL, Redis, Blade, Livewire, Alpine.js, Tailwind CSS. Dockerized, API-first, clean architecture.

## Stack

- **Backend:** Laravel 11, PHP 8.2
- **DB:** PostgreSQL 16
- **Cache/Queue:** Redis 7
- **Web:** Nginx, Blade, Livewire, Alpine.js, Tailwind CSS
- **Auth:** Laravel Sanctum (token-based API)

## Setup

1. **Init env**
   ```bash
   make init
   ```

2. **Build and install**
   ```bash
   make build
   make install
   ```
   (`make install` runs `composer install` in the app container and `php artisan key:generate`.)

3. **Start (dev, with volumes)**
   ```bash
   make dev
   ```

4. **Database: structure then data**
   ```bash
   make migrate   # tables/schema only
   make seed     # demo users, mocks, imports
   ```

5. **App**
   - Web: http://localhost:8080 (port configurable via `APP_PORT` in `.env`)
   - API: http://localhost:8080/api

## Make commands

| Command       | Description                              |
|---------------|------------------------------------------|
| `make init`   | Copy `.env.example` → `.env`             |
| `make build`  | `docker compose build`                   |
| `make rebuild`| `docker compose build --no-cache`        |
| `make install`| Composer install + key:generate          |
| `make dev`    | Start stack (dev, with volumes)          |
| `make prod`   | Start with `docker-compose.prod.yml`     |
| `make migrate`| Run migrations (schema/structure only)   |
| `make rollback`| Rollback last migration batch            |
| `make seed`   | Run seeders (demo data, mocks, imports)  |
| `make fresh`  | `migrate:fresh --seed` (drop all + migrate + seed) |
| `make backup` | PostgreSQL dump to `backup_YYYYMMDD_HHMMSS.sql`   |
| `make pgmyadmin` | Start Postgres + pgAdmin (UI on `PGADMIN_PORT`) |
| `make pgmyadmin-logs` | Show pgAdmin container logs (if it exits) |
| `make restart`| Restart containers                       |
| `make stop`   | Stop containers                          |
| `make destroy`| Down + remove volumes (asks confirm)     |

## Architecture

- **Controllers** (HTTP): call Managers only. **API controllers return JSON only; Web controllers return views only.**
- **Managers** (business logic): orchestrate Repositories and domain rules.
- **Repositories** (data): all DB access via Eloquent models.
- **Models**: Eloquent only; no business logic.

No DB or business logic in controllers; no direct DB in managers (only via repositories).

## Folder structure

```
app/
  Models/
  Repositories/
  Managers/
  Http/
    Controllers/
      Api/           # return JSON only (data)
        Admin/       # admin-only API
      Web/           # return views only (Blade)
        Admin/       # admin-only web (manage users, etc.)
  Services/          # optional, for cross-cutting services
database/
  migrations/   # schema/structure only (create tables, columns)
  seeders/      # data: mocks, demo users, imports (run via make seed)
docker/
  nginx/
  php/
```

## API

### Auth (Sanctum)

- `POST /api/login` – body: `email`, `password` (register disabled)
- `POST /api/logout` – header: `Authorization: Bearer <token>`
- `GET /api/user` – header: `Authorization: Bearer <token>`

**Demo users** (seeded by migration; password for all: `password`):

| Email             | Role  |
|-------------------|-------|
| admin1@demo.test  | Admin |
| admin2@demo.test  | Admin |
| user1@demo.test   | —     |
| user2@demo.test   | —     |
| user3@demo.test   | —     |

### Customers (protected)

- `GET /api/customers` – list (paginated, `?per_page=15`)
- `POST /api/customers` – create
- `GET /api/customers/{id}` – show
- `PUT/PATCH /api/customers/{id}` – update
- `DELETE /api/customers/{id}` – delete

Fields: `name`, `email`, `phone`, `address`.

### Admin (protected: admin role only)

**Web** (session auth): `GET /admin/users`, `GET /admin/users/{id}`, `PUT /admin/users/{id}`, `DELETE /admin/users/{id}` — manage users (list, details, edit, delete).

**API** (Bearer token + admin): `GET /api/admin/users`, `GET /api/admin/users/{id}`, `PUT /api/admin/users/{id}`, `DELETE /api/admin/users/{id}` — same data as JSON.

## Docker

- **Dev:** `docker-compose.yml` – app code mounted as volume.
- **Prod:** `docker-compose.prod.yml` – no host volume; code is in the image (build context).

**pgAdmin** (optional): `make pgmyadmin` starts Postgres + pgAdmin and **pre-configures a server** from your `.env` (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`). Open http://localhost:8282 (or `PGADMIN_PORT`), log in with `PGADMIN_DEFAULT_EMAIL` / `PGADMIN_DEFAULT_PASSWORD`. The server appears as your `APP_NAME`; no need to add it manually.

## Migrations and seeds

- **Migrations** (`database/migrations/`): schema only — create/alter tables, indexes. No demo or import data.
- **Seeders** (`database/seeders/`): data only — demo users, mocks, static imports. Run after migrations.

Commands:

- `make migrate` – run migrations (structure)
- `make rollback` – rollback last migration batch
- `make seed` – run seeders (data)
- `make fresh` – drop all tables, migrate, then seed (full reset)
- `make backup` – dump DB to `backup_YYYYMMDD_HHMMSS.sql` in project root


in case jitsi ask to login:
sudo rm -rf docker/jitsi/prosody/config/data
docker compose restart prosody jicofo jvb jitsi-web