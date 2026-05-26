# Customer Transaction Service

## Overview

The **Customer Transaction Service** is a Go-based microservice responsible for handling public-facing customer interactions related to sprints and orders in the Backlog FaaS system.

It serves as the transactional layer for customers, allowing them to:
- Browse active sprints and their available products.
- Place orders against sprint offerings (`sprint_products`).
- Automatically create customer records on first purchase.

This service is distinct from the admin panel and focuses on the customer journey.

## Purpose

This service exists to:
- Provide a clean, public API for frontend applications (especially the customer-facing site).
- Manage the order lifecycle starting from sprint selection until contract creation.
- Handle customer data capture in a low-friction way (no mandatory password on first order).
- Maintain data integrity between sprints, products, and contracts using database transactions.

## Current Features

- **Sprint Browsing**
  - Paginated list of visible sprints (`GET /sprints`)
  - Detailed view of a sprint including its products (`GET /sprints/:token`)
  - Only returns data from open and visible sprints where appropriate

- **Ordering Flow (Planned / In Progress)**
  - Place orders against specific sprint products
  - Automatic customer creation or lookup based on email
  - Atomic creation of `sprint_contracts` + `sprint_contract_items`
  - Snapshot pricing and variant data at the time of order

## API Design Principles

- Consistent response format:
  - Success: `{ "data": ... }` (with optional `meta`)
  - Error: `{ "error": { "code": "...", "message": "..." } }`
- No `success: true/false` flag (relies on HTTP status codes)
- DELETE operations return 204 No Content when successful
- All public endpoints are designed to be simple for frontend consumption

## Architecture

The service follows a **light clean architecture** (not overly layered):

```
services/customer-transaction/
├── main.go
├── config/
│   └── config.go
├── db/
│   ├── db.go                 # Connection & common utilities
│   └── sprint.go             # Sprint & sprint product queries
├── handler/
│   └── sprint_handler.go     # HTTP handlers
├── model/
│   ├── sprint.go
│   └── sprint_detail.go
```

**Key characteristics:**
- No heavy dependency injection framework
- Database logic lives in the `db/` package
- HTTP concerns are isolated in `handler/`
- Models are simple data structures (no business logic inside them)

## Data Flow (Ordering)

When a customer places an order:

1. Frontend sends `POST /sprints/:token/orders` with:
   - Selected `sprint_product_id`s + quantities
   - Customer information (name, contact, email)

2. Backend performs the following (inside a database transaction):
   - Validates that the sprint is open and visible
   - Looks up or creates a customer record (by email)
   - Creates a record in `sprint_contracts`
   - Creates one or more records in `sprint_contract_items` (with price and variant snapshot)
   - Calculates and stores total

3. On success, the order ID is returned to the frontend.

4. Customer data is suggested to be stored in `localStorage` by the frontend for future autofill.

## Database Tables Involved

This service primarily interacts with:

- `sprints`
- `sprint_products`
- `products`
- `product_variants`
- `sprint_contracts`
- `sprint_contract_items`
- `customers`

It does **not** modify core product/sprint data — it only reads them and creates contract records.

## Configuration

The service is configured entirely via environment variables (see `.env.example`):

- `SERVER_PORT`
- `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`

## Running the Service

```bash
cd services/customer-transaction
go run main.go
```

Or with environment variables:

```bash
SERVER_PORT=8889 DB_HOST=127.0.0.1 ... go run main.go
```

## Error Handling

All errors follow a consistent structure. Common error codes include:

- `INTERNAL_ERROR`
- `SPRINT_NOT_AVAILABLE`
- `INSUFFICIENT_STOCK`
- `INVALID_SPRINT_PRODUCT`

The service returns appropriate HTTP status codes (400, 404, 500, etc.).

## Future Considerations

- Customer authentication (optional login for returning users)
- Payment integration
- Order status updates
- Email notifications
- Stock reservation logic
- Rate limiting on public endpoints
- More granular product filtering and search

## Notes

- This service is intentionally kept relatively simple and focused.
- It assumes the admin panel has already prepared sprints and sprint products.
- First-time customers do not require a password — they can be created with just name, contact, and email.
- All monetary values are stored in cents (`_cents` suffix) to avoid floating point issues.

---

**Maintained by**: Backend Team  
**Last Updated**: 2026-05-26
