# AUBE TERRA Tax

Japanese-first accounting and tax management app for small businesses in Japan.

- `backend/` — Laravel 13 API (PHP 8.4)
- `design-ui/` — exported SVG design references
- `file-requirement/` — product specification documents
- `docker-compose.yml` — isolated local development stack

All in-app / user-facing copy is Japanese. Code identifiers, comments, and API
contracts are English.

## Local stack (Docker Compose)

The stack is fully isolated from the sibling `accounting-system` project:
dedicated project name, network, container names, volume, and host ports.

| Service | Container | Host port | Purpose |
| --- | --- | --- | --- |
| `mysql` | `tax_app_mysql` | `3307` → 3306 | MySQL 8.0 (`tax_app_db`) |
| `backend` | `tax_app_backend` | `8001` → 8000 | Laravel API (`php artisan serve`) |
| `phpmyadmin` | `tax_app_phpmyadmin` | `8082` → 80 | Database admin UI |

- Compose project: `tax-app`
- Network: `tax_app_net`
- Volume: `tax_app_mysql_data`

### Start

```bash
cp .env.example .env          # adjust ports if they are already taken
docker compose up -d --build
```

Run migrations and seed data on first start:

```bash
docker compose exec backend php artisan migrate:fresh --seed --force
```

You can also run them from the host using `backend/.env`
(`DB_HOST=127.0.0.1`, `DB_PORT=3307`):

```bash
cd backend
php artisan migrate:fresh --seed --force
```

### Endpoints

- Landing page: <http://localhost:8001/>
- Login API: `POST http://localhost:8001/api/auth/login`
- phpMyAdmin: <http://localhost:8082>

### Seeded accounts

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@accounting.local` | `password123` |
| Staff | `staff@accounting.local` | `password123` |

## Tests

Tests run on in-memory SQLite, so Docker is not required.

```bash
cd backend
php artisan test
```

`phpunit.xml` forces `GEMINI_API_KEY=""`, which makes the AI assistant answer
deterministically from the local intent router instead of calling Gemini.

## Stop

```bash
docker compose down            # stop containers, keep data
docker compose down -v         # also delete the data volume
```

## AI assistant architecture

`GeminiAgentService::routeIntent()` runs before the LLM and answers aggregate,
analytics, and draft-recording intents directly from the database. When nothing
matches, the request falls through to Gemini (or to a scoped refusal reply when
no API key is configured).
