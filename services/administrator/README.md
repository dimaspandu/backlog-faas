# Administrator Service

A minimal HTTP service built with Go that handles administrator-level product and sprint management for the BCSAAS platform.

## Stack

- Go 1.25
- gorilla/mux for routing
- MySQL via go-sql-driver/mysql
- CORS handled by gorilla/handlers
- Password hashing via golang.org/x/crypto/bcrypt

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | `/sessions` | Create administrator session (login) |
| GET | `/products` | List products (requires administrator session) |
| GET | `/sprints` | List sprints (requires administrator session) |

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `SERVER_PORT` | `8799` | Server listen port |
| `DB_HOST` | `127.0.0.1` | MySQL host |
| `DB_PORT` | `3306` | MySQL port |
| `DB_USER` | `root` | MySQL username |
| `DB_PASSWORD` | `` | MySQL password |
| `DB_NAME` | `bcsaas` | MySQL database name |
| `CORS_ALLOWED_ORIGINS` | `http://localhost:4500,https://backlog.deduksi.com` | Comma-separated list of allowed origins |

## Local Development

```bash
go mod download
go run ./cmd/api
```

The service expects a `.env` file in the project root. Copy `.env.example` to `.env` and adjust values as needed.

## Seed Admin

A seed helper is available under `cmd/seed-admin/` to bootstrap the initial administrator user:

```bash
go run ./cmd/seed-admin
```

## Docker

Build the image:

```bash
docker build -t bcsaas-administrator .
```

Run the container:

```bash
docker run -d -p 8799:8799 \
  -e DB_HOST=mysql \
  -e DB_PORT=3306 \
  -e DB_USER=root \
  -e DB_PASSWORD=secret \
  -e DB_NAME=bcsaas \
  -e CORS_ALLOWED_ORIGINS=http://localhost:4500 \
  bcsaas-administrator
```

## Database Schema

See the `db/mysql/` directory for the required schema and seed data.
