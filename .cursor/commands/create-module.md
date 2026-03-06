# create-module


Create a full CRUD module for the provided entity.

The module must follow Laravel 12 + Vue 3 best practices.

## Backend

Generate:

- Migration
- Model
- Factory
- Form Requests (Store + Update)
- Controller
- API Resource
- Policy
- Routes
- Feature Tests

Controllers must remain thin.

Move complex logic into:

app/Services  
or  
app/Actions

Use:

- Eloquent relationships
- Route model binding
- Pagination
- API resources

## Frontend (Vue 3)

Generate Vue pages using Composition API and `<script setup>`.

Structure:

pages/  
components/  
services/  
composables/

Example files:

pages/StudentsPage.vue  
components/StudentTable.vue  
components/StudentForm.vue  

## UI Requirements

Tables must include:

- pagination
- search
- filters
- loading state
- empty state

Forms must include:

- validation
- loading state
- success feedback
- error handling

## Output

Return:

- backend files created
- frontend files created
- routes
- API endpoints

This command will be available in chat with /create-module
