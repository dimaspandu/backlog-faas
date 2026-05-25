# Database Layer (Piedpi)

## Purpose

The database layer in Piedpi is designed as a **safe, minimal, and explicit abstraction** over PDO. It intentionally avoids ORM complexity while still enforcing strong engineering boundaries.

This layer exists to:

* Prevent SQL and driver errors from leaking upward
* Enforce prepared statements by default
* Provide a stable contract for application code
* Keep database logic predictable and testable

---

## Design Principles

1. **Prepared statements only**
2. **No SQL exposure outside the database boundary**
3. **Explicit transaction handling**
4. **Single shared connection**
5. **No hidden magic or reflection**

This design mirrors how mature frameworks isolate their database internals, but in a form that is easy to audit and understand.

---

## Public API

### `DB::query(string $sql, array $params = []): array`

Execute a SELECT query and return all rows.

```php
$users = DB::query(
  'SELECT * FROM users WHERE status = :status',
  ['status' => 'active']
);
```

---

### `DB::one(string $sql, array $params = []): ?array`

Fetch a single row or `null`.

```php
$user = DB::one(
  'SELECT * FROM users WHERE id = :id',
  ['id' => 1]
);
```

---

### `DB::exec(string $sql, array $params = []): int`

Execute a write operation and return affected rows.

```php
$count = DB::exec(
  'UPDATE users SET active = 0 WHERE last_login < :date',
  ['date' => '2024-01-01']
);
```

---

### `DB::transaction(callable $callback): mixed`

Run multiple operations inside a transaction.

```php
DB::transaction(function () {
  DB::exec('INSERT INTO logs(message) VALUES (:msg)', ['msg' => 'test']);
  DB::exec('UPDATE counters SET value = value + 1');
});
```

If any exception occurs, the transaction is rolled back automatically.

---

## Error Handling Contract

* All PDO errors are wrapped in `DatabaseException`
* SQL statements and driver messages are never exposed
* Upper layers only deal with generic failures

This guarantees production safety and consistent error handling.

---

## Testing Strategy

### Mock-Based Testing

Database tests should **not require a real database**.

Instead, tests validate:

* API contracts
* Exception boundaries
* Transaction behavior

Mocking is achieved by replacing the PDO connection internally during tests.

This ensures:

* Fast test execution
* Deterministic results
* No external dependencies

A real database can be added later for integration tests if needed.

---

## Anti-Patterns (Avoid)

* Executing raw PDO outside this layer
* Building SQL dynamically without parameters
* Catching PDOException in controllers
* Returning PDOStatement objects

Violating these rules breaks isolation and safety guarantees.

---

## Growth Path

This database layer can evolve to support:

* Query builder
* Read/write connection split
* Connection pooling
* Observability hooks

All without breaking existing application code.
