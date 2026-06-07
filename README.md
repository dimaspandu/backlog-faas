# Backlog FaaS

Monorepo for the backend services powering backlog.deduksi.com.

## Architecture

- **db/mysql/** — Database schema (MySQL/MariaDB)
  - `schema.sql` — Clean schema without seed data
  - Tables: `inventory`, `products`, `product_recipes`, `sprints`, `sprint_product_offerings`, `customers`, `sprint_contracts`, `sprint_contract_orders`, `admins`, `admin_sessions`

- **services/administrator/** — Administrator service (Go)
  - Sprint management API
  - Port: 8799

- **services/backoffice/** — Backoffice service (Go)
  - Admin authentication and management
  - Port: 8699

- **services/customer-transaction/** — Customer-facing transaction service (Go)
  - Browse active sprints and available products
  - Place orders from sprint offerings (creates `sprint_contracts` + `sprint_contract_orders`)
  - Automatic customer creation on first purchase
  - Standardized REST responses (`{ data, meta }` envelope)
  - Port: 8899

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
# Configure DB_NAME in .env
go run cmd/api/main.go
```

### Backoffice Service

```bash
cd services/backoffice
cp .env.example .env
# Configure DB_NAME in .env
go run cmd/api/main.go
```

### Customer Transaction Service

```bash
cd services/customer-transaction
cp .env.example .env
# Configure DB_NAME in .env
go run main.go
```

## Environment Variables

All services use the same database configuration pattern:

| Variable | Description | Default |
|----------|-------------|---------|
| `SERVER_PORT` | HTTP server port | Service-specific |
| `DB_HOST` | Database host | 127.0.0.1 |
| `DB_PORT` | Database port | 3306 |
| `DB_USER` | Database user | root |
| `DB_PASSWORD` | Database password | (empty) |
| `DB_NAME` | Database name | bcsaas |

## Key Conventions

- Conventional commits (`feat:`, `fix:`, `chore:`)
- Soft deletes via `status` columns
- Session-based authentication for admin services
- SKU resolution priority in sprint offerings: explicit → variant → product