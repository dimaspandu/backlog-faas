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

- **services/customer-transaction/** — Public customer-facing transaction service (Go)
  - Browse active sprints and their available products
  - Place orders from sprint offerings (creates `sprint_contracts` + `sprint_contract_items`)
  - Automatic customer creation on first purchase (name, contact, email)
  - Standardized REST responses using `{ data, meta }` envelope pattern

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

### Admin Panel
- Full CRUD for Sprints, Products, Product Variants, and Sprint Products
- Cleaned up from boilerplate (removed unused controllers/routes)

### Customer Transaction Service (`services/customer-transaction`)
- New public service for customer ordering flow
- Standardized API response format (`{ data, meta }` envelope)
- Pagination support on sprint listing
- Clean architecture (config / db / handler / model separation)
- Foundation ready for customer order creation (`sprint_contracts` + `sprint_contract_items`)

### Documentation
- Added detailed README for customer-transaction service
- Established consistent API response standards across services