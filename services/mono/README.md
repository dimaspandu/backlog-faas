# MongoDB Mono Service

A minimal, framework-free PHP API service for the BCSAAS monolith backend. It exposes product, sprint, and sprint contract endpoints used by the frontend and other internal services.

## Stack

- PHP 8.3 CLI
- Custom micro-routing framework (no Composer dependency)
- MySQL via PDO
- CORS middleware included
- `.htaccess` URL rewriting for Apache deployments

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/` | Health check |
| GET | `/products` | List active products |
| GET | `/products/:id/active-sprints` | List active sprints for a product |
| GET | `/sprints` | List visible sprints |
| GET | `/sprints/:token` | Show sprint details with product offerings |
| POST | `/sprints/:token/contracts` | Create a new contract |
| GET | `/sprints/:token/contracts` | List contracts for a sprint |
| GET | `/sprints/:token/contracts/:contractNumber` | Show contract details |

Sprint contract endpoints require a valid sprint token via the path parameter. All endpoints return JSON.

## Request Body (Create Contract)

```json
{
  "products": [
    {
      "id": 1,
      "sugarLevel": "NONE"
    }
  ],
  "customerName": "John Doe",
  "customerContact": "john@example.com",
  "customerAuthProvider": "GUEST",
  "customerExternalId": "optional-id",
  "notes": "Optional order notes"
}
```

`customerContact` must be a valid email or phone number. `products` must contain at least one valid product ID.

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_ENV` | `development` | Application environment |
| `APP_DEBUG` | `true` | Enable debug mode |
| `APP_BASE_PATH` | `` | Base path prefix |
| `APP_BASE_URL` | `http://localhost:9999` | Public base URL |
| `DB_HOST` | `127.0.0.1` | MySQL host |
| `DB_PORT` | `3306` | MySQL port |
| `DB_NAME` | `` | MySQL database name |
| `DB_USER` | `` | MySQL username |
| `DB_PASS` | `` | MySQL password |
| `CORS_ALLOWED_ORIGINS` | `http://localhost:4500,https://backlog.deduksi.com` | Allowed CORS origins |

## Local Development

Build/compile is not required. The service runs directly via PHP's built-in server:

```bash
php -S localhost:9999 -t .
```

The service expects a `.env` file in the project root. Copy `.env.example` to `.env` and adjust values as needed.

## Database

The database schema is located at `db/mysql/schema.sql` and seed data at `db/mysql/seeding.sql`.

## Docker

Build the image:

```bash
docker build -t bcsaas-mono .
```

Run the container:

```bash
docker run -d -p 9999:9999 \
  -e DB_HOST=mysql \
  -e DB_PORT=3306 \
  -e DB_USER=root \
  -e DB_PASS=secret \
  -e DB_NAME=bcsaas \
  -e CORS_ALLOWED_ORIGINS=http://localhost:4500 \
  bcsaas-mono
```

## Production Notes

- The Docker image runs PHP's built-in server. For higher traffic, replace the `CMD` with a process manager such as `php-fpm` behind a reverse proxy.
- Ensure `storage/` is writable by the web process.
- Keep `.env` and other configuration files outside the web root or deny access via `.htaccess` (already included).

## License

MIT
