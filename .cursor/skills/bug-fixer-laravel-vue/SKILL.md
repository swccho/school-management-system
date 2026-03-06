---
name: bug-fixer-laravel-vue
description: Diagnose and fix bugs in Laravel + Vue applications. Use when debugging API errors, validation, state, or frontend-backend integration issues.
---

# Laravel Vue Bug Fixer

Investigate and fix issues across backend and frontend.

## Steps

1. **Reproduce bug** — Minimal steps; note request/response or component state when it appears.
2. **Inspect backend logs** — Laravel log, stack traces, and HTTP status codes.
3. **Check API responses** — Status, JSON shape, validation `errors` object; confirm contract with frontend.
4. **Verify validation rules** — Form Request rules vs frontend; consistent error shape (e.g. `errors` key).
5. **Inspect frontend state** — Loading/error/success after API calls; ref/reactive usage; stale data or double submit.
6. **Identify root cause** — Backend (PHP/Laravel), frontend (Vue/JS), or integration (payload/response handling).

## Backend (Laravel)

- **Validation**: Form Request rules; return consistent error shape.
- **Authorization**: Policy/Gate; 403 with clear message when unauthorized.
- **Data**: Model relationships, eager loading (N+1), casts; JSON/API Resource matches what frontend expects.
- **Errors**: Log details server-side; avoid exposing internals in production.

## Frontend (Vue)

- **API**: URL, method, payload; handle 422, 403, 404, 500 and show message or field errors.
- **State**: Correct loading/error/success after calls; avoid stale data or double submissions.
- **Reactivity**: ref/reactive correct; list updates (create/delete) reflect in UI.

## Integration

- Request/response contract: Content-Type, JSON keys, status codes.
- Auth (token/session) sent when required; handle 401 (redirect or login prompt).

## Fix Strategy

- Prefer **minimal fixes**; change only what is necessary.
- **Avoid unnecessary refactors**; do not rewrite working code.
- **Document** in the response:
  - **Root cause** — What actually caused the bug.
  - **Fix applied** — What was changed and where.
  - **Potential side effects** — Any risks or follow-up to consider.
