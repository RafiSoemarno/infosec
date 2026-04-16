# Docker Setup

This document describes how to run the **infosec** Laravel 8 application locally using Docker (nginx + PHP-FPM).

---

## Prerequisites

- [Docker Desktop for Windows](https://docs.docker.com/desktop/install/windows-install/) (includes Docker Compose)
- A `.env` file in the project root (copy `.env.example` and fill in the values)

---

## Architecture

| Container | Image | Role |
|---|---|---|
| `infosec-app` | Built from `Dockerfile` (php:8.2-fpm) | PHP-FPM runtime for Laravel |
| `infosec-nginx` | `nginx:1.27-alpine` | Web server, proxies PHP requests to `app:9000` |

Databases (MariaDB and SQL Server Express) run on the **host machine** and are reached by the containers via `host.docker.internal` (resolved automatically by Docker Desktop on Windows).

---

## Environment variables

Copy `.env.example` to `.env` and fill in your values:

```dotenv
APP_NAME=infosec
APP_ENV=local
APP_KEY=                        # generate once with: docker compose exec app php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8080

# Primary database — MariaDB (mysql driver)
DB_CONNECTION=mysql
DB_HOST=host.docker.internal    # host machine on Windows/macOS Docker Desktop
DB_PORT=3306
DB_DATABASE=your_mariadb_db
DB_USERNAME=your_mariadb_user
DB_PASSWORD=your_mariadb_password

# Second database — SQL Server Express (sqlsrv2 connection in config/database.php)
DB_SQLSRV_HOST=host.docker.internal
DB_SQLSRV_PORT=1433
DB_SQLSRV_DATABASE=your_sqlserver_db
DB_SQLSRV_USERNAME=sa
DB_SQLSRV_PASSWORD=your_sqlserver_password
```

> **Note:** SQL Server Express must be configured to use **SQL Server Authentication** (not Windows Authentication). Windows Authentication is not supported from Linux containers.

> **Note:** If your SQL Server Express instance uses ODBC Driver 18 defaults, you may need to add `TrustServerCertificate=true` to the connection options in `config/database.php` if the connection refuses due to self-signed certificate errors.

---

## Build and run

```bash
# Build the PHP-FPM image and start both containers in the background
docker compose up -d --build

# Verify both containers are running
docker compose ps
```

The application is available at **http://localhost:8080**.

---

## First-time setup

### Generate app key (only needed once)

```bash
docker compose exec app php artisan key:generate
```

### Run database migrations (manual — not automatic)

Migrations are intentionally **not** run automatically on container startup. Run them manually when you are ready:

```bash
# Primary (MariaDB)
docker compose exec app php artisan migrate

# If you have migrations targeting the second connection
docker compose exec app php artisan migrate --database=sqlsrv2
```

---

## Common commands

```bash
# View logs
docker compose logs -f

# Open a shell inside the PHP-FPM container
docker compose exec app bash

# Run artisan commands
docker compose exec app php artisan <command>

# Stop containers
docker compose down

# Rebuild image after Dockerfile or dependency changes
docker compose up -d --build
```

---

## Using the second SQL Server connection

In `config/database.php` a second connection named `sqlsrv2` is defined, driven by `DB_SQLSRV_*` environment variables.

Use it in your Eloquent models:

```php
protected $connection = 'sqlsrv2';
```

Or via the query builder:

```php
DB::connection('sqlsrv2')->select('SELECT 1');
```

---

## Production deployment

For deployment to a production VM:

1. Build the image on the VM (or push to a registry and pull):

   ```bash
   docker compose build app
   ```

2. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
3. Remove bind mounts from `docker-compose.yml` (rely on the image's baked-in code).
4. Add a named volume for `storage/` to persist uploads and logs across container restarts:

   ```yaml
   volumes:
     - infosec-storage:/var/www/html/storage
   ```

5. Place nginx behind a TLS-terminating reverse proxy or load balancer.

---

## Storage

The `storage/` and `bootstrap/cache/` directories are owned by `www-data` inside the container and are writable. In local development they are bind-mounted from the host, so changes persist automatically. In production, use a Docker named volume (see above).
