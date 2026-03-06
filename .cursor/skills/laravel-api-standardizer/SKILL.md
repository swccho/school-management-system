---
name: laravel-api-standardizer
description: Enforce consistent API response structure in Laravel. Use when defining or refactoring API endpoints, error handling, response formats, or validation responses.
---

# Laravel API Standardizer

Ensure APIs follow a consistent structure across all endpoints.

## Success Response

```json
{
  "success": true,
  "message": "Success",
  "data": {}
}
```

- `data`: payload (object or array). Never return raw Eloquent models—use API Resources.

## Error Response

```json
{
  "success": false,
  "message": "Error message"
}
```

Use appropriate HTTP status codes (403, 404, 500, etc.).

## Validation Error

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {}
}
```

- `errors`: object with field names as keys and arrays of message strings.
- Use HTTP status `422` for validation failures.

## API Rules

**Always use:**

- **API Resources** — transform models before returning; never return raw models.
- **Form Requests** — validate input and keep controllers thin.
- **Route Model Binding** — resolve models in routes; return 404 when not found.

**Never:** return raw Eloquent models or collections directly.

## Pagination Format

```json
{
  "success": true,
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 100
  }
}
```

- Use Laravel's `paginate()` and wrap the result in the standard shape with `data` and `meta` (or use `ResourceCollection` with `->additional(['meta' => ...])`).

## Performance

- **Eager load** relations used in the response (e.g. `with()`) to avoid N+1 queries.
- **Paginate** list endpoints; do not return unbounded collections.
- **Avoid N+1** — ensure all displayed relation data is loaded in the initial query.
