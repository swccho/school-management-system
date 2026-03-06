---
name: laravel-feature-builder
description: Generate a complete Laravel backend feature following Laravel 12 best practices.
---

# Laravel Feature Builder

This skill generates a complete Laravel backend feature.

## Steps

1. Create Migration
2. Create Model
3. Create Factory
4. Create Seeder if needed
5. Create Form Request validation
6. Create Controller
7. Create Policy
8. Create Service or Action class if business logic grows
9. Register API routes
10. Create API Resource
11. Write Feature Tests

## Controller Rules

Controllers must remain thin.

Controllers should only:

- receive request
- call service or action
- return response or resource

No business logic inside controllers.

## Validation

Always use Form Request classes.

## Business Logic

Move complex logic into:

app/Services  
or  
app/Actions

## Database

Use:

- Eloquent relationships
- Route model binding
- Database transactions for multi-step writes
- Eager loading to prevent N+1 queries

## API Response

All APIs must return consistent JSON.

Example:

```json
{
  "success": true,
  "data": {},
  "message": ""
}
```

## Deliverables

When generating code list:

- files created
- routes added
- validation rules
- edge cases
- tests required
