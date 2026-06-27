# Customer Transaction Service

A minimal HTTP service built with Go that manages products and sprint contract transactions for the BCSAAS platform.

## Stack

- Go 1.25
- gorilla/mux for routing
- MySQL via go-sql-driver/mysql
- CORS handled by gorilla/handlers

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/products` | List products |
| GET | `/products/{id}/active-sprints` | List active sprints for a product |
| GET | `/sprints` | List sprints |
| GET | `/sprints/{token}` | Show sprint details |
| POST | `/sprints/{token}/contracts` | Create a new contract |
| GET | `/sprints/{token}/contracts` | List contracts for a sprint |
| GET | `/sprints/{token}/contracts/{contractNumber}` | Show contract details |

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `PORT` | `8899` | Server listen port |
| `DB_HOST` | `127.0.0.1` | MySQL host |
| `DB_PORT` | `3306` | MySQL port |
| `DB_USER` | `root` | MySQL username |
| `DB_PASSWORD` | `` | MySQL password |
| `DB_NAME` | `bcsaas` | MySQL database name |
| `CORS_ALLOWED_ORIGINS` | `http://localhost:4500,https://backlog.deduksi.com` | Comma-separated list of allowed origins |

## Local Development

```bash
go mod download
go run main.go
```

The service expects a `.env` file in the project root. Copy `.env.example` to `.env` and adjust values as needed.

## Docker

Build the image:

```bash
docker build -t bcsaas-customer-transaction .
```

Run the container:

```bash
docker run -d -p 8899:8899 \
  -e DB_HOST=mysql \
  -e DB_PORT=3306 \
  -e DB_USER=root \
  -e DB_PASSWORD=secret \
  -e DB_NAME=bcsaas \
  -e CORS_ALLOWED_ORIGINS=http://localhost:4500 \
  bcsaas-customer-transaction
```

## Database Schema

See the `db/mysql/` directory for the required schema and seed data.
