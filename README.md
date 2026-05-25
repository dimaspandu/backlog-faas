# Backlog FaaS

Monorepo for the backend services powering backlog.deduksi.com.

## Architecture

- **admin/** — PHP admin panel (modernized)
  - Sprint, Product, Product Variant, and Sprint Product (offering) management
  - RESTful API under `/api/admin/*`
  - Soft deletes, status workflows, and secure session-based auth
  - Tests: `admin/tests/` (run via `php tests/run.php`)

- **db/mysql/** — Database schema and incremental migrations

- **services/mono/** — GraphQL service (Node.js/TypeScript) for public sprint data

- **services/transaction/** — Transaction/order service (Go)

## Getting Started (Admin)

1. Copy `.env.example` → `.env` in `admin/`
2. Configure database credentials (all via environment variables)
3. Run the admin locally (typically via `php -S` or Apache/Nginx pointing to `public/`)
4. Run tests:
   ```bash
   cd admin
   php tests/run.php
   ```

## Key Conventions

- Conventional commits (`feat:`, `fix:`, `chore:`)
- Explicit middleware (no magic pipelines)
- Soft deletes via `status` columns
- SKU resolution priority in sprint offerings: explicit → variant → product

## Project Status (2026)

The admin panel has been actively developed with full CRUD for:
- Sprints
- Products + Variants
- Sprint Products (offerings with pricing/stock)

Boilerplate example routes and unused controllers have been removed for a cleaner codebase.