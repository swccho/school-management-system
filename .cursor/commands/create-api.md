# create-api

Create a clean Laravel API endpoint.

Follow Laravel 12 best practices.

## Generate

- Route
- Controller method
- Form Request validation
- API Resource
- Feature tests

## API Rules

Use:

- Form Request validation
- Route model binding
- API resources

Never return raw models.

## Response Format

Success:

{
success: true,
message: "",
data: {}
}

Validation error:

{
success: false,
message: "Validation failed",
errors: {}
}

## Performance

Always:

- eager load relationships
- paginate lists
- prevent N+1 queries

This command will be available in chat with /create-api
