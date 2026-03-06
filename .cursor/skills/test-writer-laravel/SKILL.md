---
name: test-writer-laravel
description: Generate PHPUnit tests for Laravel applications. Use when adding or refactoring tests for endpoints, services, actions, or when the user asks for test coverage.
---

# Laravel Test Writer

Generate PHPUnit feature and unit tests for Laravel applications.

## Test Types

### Feature Tests

Use for:

- API endpoints
- Authentication flows
- Authorization (policies, middleware)
- Validation (request rules, error responses)

### Unit Tests

Use for:

- Services
- Actions
- Helpers and pure functions

## Coverage

For each endpoint (or equivalent flow), ensure tests cover:

- **Success case** — expected status, response shape, side effects (DB, mail, etc.)
- **Validation failure** — invalid/missing input, assert 422 and error structure
- **Unauthorized access** — unauthenticated or forbidden (401/403) when applicable
- **Edge cases** — not found (404), empty results, boundary values

For unit tests: one logical behavior per test; cover happy path and important failure/edge cases.

## Tools

Use:

- **Model factories** — build test data; avoid hardcoding IDs
- **Database refresh** — `RefreshDatabase` or transactions so tests stay isolated
- **HTTP testing** — `$this->get()`, `$this->post()`, `$response->assertStatus()`, `$response->assertJsonStructure()`, etc.

## Conventions

- Feature tests in `tests/Feature`, unit tests in `tests/Unit`.
- Descriptive method names (e.g. `it_returns_401_when_unauthenticated`).
- Prefer factory state over manual model creation.
- Mock external dependencies (HTTP, filesystem) in unit tests when needed.

## Deliverables

When adding or changing tests, list:

- Test files and classes added or updated
- Cases covered (success, validation, auth, not found, edge)
- Any setup used (factories, seeders, traits)
