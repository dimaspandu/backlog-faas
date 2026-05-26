# Changelog

## [1.0.7] – Documentation Overhaul & Middleware Usage Examples

### Added
- Comprehensive README.md overhaul with accurate architecture and structure
- New **Middleware** section documenting explicit usage patterns (routes + controller)
- New **Architecture Diagram** using high-quality PlantUML (replaces previous Mermaid)
- New **Project Directory Structure** section with complete root-level layout
- Documentation for dual web roots: `public/` (development/VPS) and `public_html/` (shared hosting)
- Documentation for feature branches: `frontend-services` and `backend-services`
- Example middleware: `AuthMiddleware` (authentication guard) and `CsrfMiddleware` (state-changing protection)
- Practical middleware usage demonstrations in `routes/web.php` and `routes/api.php`
- Alternative pattern showing middleware calls from inside controller methods (`ItemController`)

### Changed
- High-Level Architecture section corrected and expanded
- Request Lifecycle updated to reflect both `public/` and `public_html/` entry points
- Running the Project section enhanced with shared hosting deployment notes

### Notes
This release significantly improves project documentation and provides concrete, realistic examples of middleware usage while maintaining Piedpi’s core philosophy of **explicit control flow** and avoiding hidden magic or framework-style pipelines.

---

## [1.0.6] – Database Configuration & PDO Abstraction Foundation

### Added
- Environment-based database configuration via `config/database.php`
- Centralized PDO connection factory (`Connection::get()`)
- Safe static database facade (`DB`) for read-only queries
- Development-only database test route (`/_debug/db/items`)
- Explicit separation between:
  - Configuration
  - Connection lifecycle
  - Query execution

### Improved
- Removal of hardcoded database credentials from application logic
- Consistent environment access via `env()` across app & database layers
- Safer database experimentation without affecting production routes
- Clear upgrade path from raw PDO usage to structured DB abstraction

### Notes
This release introduces the **database foundation layer** of Piedpi.

Instead of tightly coupling controllers to PDO, database access is now:
- Centralized
- Environment-aware
- Replaceable without touching controllers

The new DB facade intentionally exposes **minimal surface area**:
- No ORM
- No query builder
- No magic state

This preserves Piedpi’s core principles:
- Explicit data flow
- Predictable execution
- Zero hidden coupling

While laying the groundwork for:
- Transactions
- Write helpers
- Optional query helpers
- Future persistence backends

---

## [1.0.5] – Custom Error Routing & Explicit Failure Control

### Added
- Router-level custom 404 (Not Found) handler via `setNotFoundHandler()`
- Router-level custom 500 (Internal Server Error) handler via `setErrorHandler()`
- Ability to render error pages using standard controllers
- Exception injection into 500 error handlers for diagnostic or logging needs

### Improved
- Explicit separation between:
  - Route resolution
  - Handler execution
  - Error recovery paths
- Router resilience against uncaught exceptions during handler execution
- Developer control over error presentation without overriding global handlers
- Consistency between normal routes and error flows

### Notes
This release formalizes **error handling as a first-class routing concern**.

Instead of treating errors as side effects, Piedpi now models them as
**explicit execution paths**, allowing custom error pages to be composed,
streamed, or served just like normal routes.

This design preserves:
- Streaming compatibility
- Framework-free architecture
- Predictable control flow

While significantly improving real-world production readiness.

---

## [1.0.4] – Static Asset Serving & Gzip Support

### Added
- Native gzip response support in `Renderer::serve()`
- Content negotiation based on `Accept-Encoding`
- Direct serving of full HTML, CSS, and JS files
- Optional gzip toggle per response
- Automatic `Content-Length` and `Vary: Accept-Encoding` headers

### Improved
- Separation between streamed views and static asset delivery
- Support for modern frontend bundles without external servers
- HTTP efficiency for single-file HTML applications
- Compatibility with micro and monolithic deployments

### Notes
This release extends Piedpi beyond streaming views into a
**hybrid rendering model**.

The system now supports:
- Progressive HTML streaming (BigPipe-style)
- Full static document delivery
- Optional gzip compression

All without introducing a build step, dependency, or framework-level
asset pipeline.

---

## [1.0.3] – View Layer Refinement & Widget Enhancement

### Added
- Self-closing HTML tag support in Widget renderer (`br/`, `hr/`, etc.)
- Recursive widget children handling (string, array, nested widgets)
- Safe mixed-content rendering (escaped text + trusted widget output)
- Asset routing support for non-public front controller setups

### Improved
- Widget rendering flexibility for complex semantic layouts
- HTML composition without introducing a template engine
- View-layer ergonomics for progressive HTML streaming
- Consistency between streamed chunks and full view rendering

### Notes
This release strengthens the **view and composition layer** of Piedpi.
Widgets can now express complex, semantic HTML structures while
remaining framework-free, stream-safe, and predictable.

---

## [1.0.2] – HTTP Layer & Middleware Completion

### Added
- Error boundary middleware for safe request execution
- Full HTTP response abstraction (JSON, Stream, File)
- Support for HTTP methods beyond GET (POST, PUT, PATCH, DELETE)
- API routing separation (`routes/web.php` & `routes/api.php`)
- Health check endpoint (`/health`)
- Basic request payload handling (JSON body & query string)

### Improved
- Router execution flow with centralized response sending
- Controller clarity between web and API responses
- Public entrypoint readability and route organization

### Notes
This release completes the **HTTP and middleware layer** of Piedpi.
The system now supports REST-style APIs while keeping rendering
explicit and framework-free.

---

## [1.0.1] - Database Core

### Added
- Safe PDO wrapper
- Centralized database connection
- Transaction boundary
- Database exception isolation

---

## [1.0.0] - Initial Release

### Added
- Trie-based HTTP router with dynamic parameters
- Controller-based architecture
- Progressive HTML streaming via Renderer
- File-based and chunk-based view rendering
- Simple widget/component rendering system
- Centralized error handling (404 / 500)
- Minimal PSR-4–like autoloader
- Zero-dependency test suite

### Philosophy
- Minimal surface area
- Explicit over implicit
- MVP-ready but scalable
- No unnecessary abstractions
