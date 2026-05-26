# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- New `services/customer-transaction` service (Go)
  - Public endpoints for browsing sprints and sprint products
  - Standardized REST response format using `{ "data", "meta" }` envelope
  - Pagination support for sprint listing
  - Clean architecture structure (config, db, handler, model)
- Detailed `README.md` for the customer-transaction service
- Consistent error response format across endpoints

### Changed
- Root `README.md` updated to reflect current monorepo state and recent development on customer transaction capabilities
- Improved documentation around API design conventions

### Removed
- Various boilerplate code and unused controllers from the admin panel (previous work)

## [0.1.0] - 2026-05-01

### Added
- Initial monorepo structure
- Admin panel (PHP) with basic sprint and product management
- Database schema and migrations
- `services/mono` (GraphQL service)
- `services/transaction` (Go order service)

[Unreleased]: https://github.com/your-org/backlog-faas/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/your-org/backlog-faas/releases/tag/v0.1.0
