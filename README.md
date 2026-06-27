# Backlog FaaS

Backend services engine powering the Backlog Coffee Sprint as a Service (BCSAAS) platform at [https://backlog.deduksi.com/](https://backlog.deduksi.com/).

## Architecture

- **db/mysql/** — Database schema (MySQL/MariaDB)
  - `schema.sql` — Clean schema without seed data
  - Tables: `inventory`, `products`, `sprint_product_offerings`, `sprints`, `customers`, `sprint_contracts`, `sprint_contract_orders`, `admins`, `admin_sessions`

- **services/administrator/** — Administrator service (Go)
  - Sprint and product management API
  - Session-based admin authentication
  - Port: 8799

- **services/backoffice/** — Backoffice service (Go)
  - Contract processing workflow
  - Session-based admin authentication
  - Port: 8699

- **services/customer-transaction/** — Customer-facing transaction service (Go)
  - Public endpoints for browsing sprints and placing orders
  - Clean architecture (config, db, domain, handlers)
  - Standardized REST responses (`{ data, meta }` envelope)
  - Port: 8899

- **services/mono/** — Legacy PHP monolith service
  - REST endpoints for products, sprints, and contracts
  - Port: 9999 (PHP built-in server)

## Getting Started

### Database Setup

Import the schema to MySQL/MariaDB:

```sql
mysql -u root -p < db/mysql/schema.sql
```

### Administrator Service

```bash
cd services/administrator
cp .env.example .env
# Configure DB_PASSWORD in .env
go run ./cmd/api
```

### Backoffice Service

```bash
cd services/backoffice
cp .env.example .env
# Configure DB_PASSWORD in .env
go run ./cmd/api
```

### Customer Transaction Service

```bash
cd services/customer-transaction
cp .env.example .env
# Configure DB_PASSWORD in .env
go run main.go
```

### Mono Service

```bash
cd services/mono
cp .env.example .env
# Configure DB_PASS in .env
php -S localhost:9999 -t .
```

## Environment Variables

### Go Services

| Variable | Description | Default |
|----------|-------------|---------|
| `SERVER_PORT` / `PORT` | HTTP server port | Service-specific |
| `DB_HOST` | Database host | 127.0.0.1 |
| `DB_PORT` | Database port | 3306 |
| `DB_USER` | Database user | root |
| `DB_PASSWORD` | Database password | (empty) |
| `DB_NAME` | Database name | bcsaas |
| `CORS_ALLOWED_ORIGINS` | Comma-separated allowed origins | http://localhost:4500,https://backlog.deduksi.com |

### Mono Service

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_ENV` | Application environment | development |
| `APP_DEBUG` | Debug mode | true |
| `APP_BASE_PATH` | Base path prefix | (empty) |
| `APP_BASE_URL` | Public base URL | http://localhost:9999 |
| `DB_HOST` | Database host | 127.0.0.1 |
| `DB_PORT` | Database port | 3306 |
| `DB_NAME` | Database name | (empty) |
| `DB_USER` | Database user | (empty) |
| `DB_PASS` | Database password | (empty) |

## Key Conventions

- Conventional commits (`feat:`, `fix:`, `chore:`)
- Soft deletes via `status` columns
- Session-based authentication for admin services
- REST response format: `{ data, meta }` envelope
- Sprint contract flow: request → payment → fulfillment