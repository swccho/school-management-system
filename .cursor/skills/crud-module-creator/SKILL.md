---
name: crud-module-creator
description: Generates full CRUD modules for admin systems using Laravel and Vue 3. Use when adding a new resource (e.g. students, teachers, notices, events, subjects, classes, gallery) that needs list, create, edit, and delete with search, filters, pagination, and validation.
---

# CRUD Module Creator

Generate a complete CRUD module: Laravel backend plus Vue 3 frontend (pages and components). Apply when the user asks for a new admin resource, entity, or module.

## Module Examples

Students · Teachers · Notices · Events · Subjects · Classes · Gallery

---

## Backend

Create in order:

| Artifact | Purpose |
|----------|---------|
| **Migration** | Table with `workspace_id` (if multi-tenant), indexes on filter/sort columns, foreign keys with cascade where appropriate |
| **Model** | Fillable, casts, relationships, optional global scope for workspace |
| **Form Requests** | `StoreXxxRequest`, `UpdateXxxRequest` — validation rules only |
| **Controller** | Thin: index (paginated), store, show, update, destroy; delegate business logic to Services |
| **API Resource** | Single and collection transforms for consistent API response shape |
| **Policy** | Authorize index/store/update/destroy; scope by workspace if multi-tenant |
| **Routes** | RESTful, named; register in appropriate route file (e.g. `api.php` or web API group) |

Conventions:

- Controllers handle HTTP only; put logic in Services/Actions.
- All writes: validate via Form Request, authorize via Policy.
- Use typed properties and return types; no `any`-style logic.
- Use database transactions for operations that touch multiple tables.

---

## Frontend

### File Layout

- **Pages**: one per screen, e.g. `pages/StudentsPage.vue`, `pages/StudentCreatePage.vue`, `pages/StudentEditPage.vue`, (optional) `pages/StudentViewPage.vue`.
- **Components**: reusable table and form, e.g. `components/StudentTable.vue`, `components/StudentForm.vue`.

Use Composition API and `<script setup>`. Call API via a composable or dedicated service; keep pages thin.

### Required Screens

#### List Page

Must include:

- **Search** — text input that filters list (debounced if calling API).
- **Filters** — dropdowns or controls for status, date range, category, etc., as needed.
- **Pagination** — page size and page navigation; sync with URL query params when appropriate.
- **Sorting** — sortable column headers or sort dropdown.
- **Actions** — create button; per-row actions: edit, delete (with confirm), optional view.

#### Create Page

Must include:

- Form with all required fields.
- **Validation** — show field-level errors from API or client-side rules.
- **Loading state** — disable submit and show spinner/loading during submit.
- **Error handling** — show global error message or toast on failure; redirect or success message on success.

#### Edit Page

Must include:

- **Prefilled data** — load record by id and bind to form.
- Same as Create: **validation**, **loading state**, **error handling**.

#### View Page (optional)

Read-only page showing one record. No form submission; optional “Edit” button linking to Edit page.

---

## UI Rules

### Tables

- **Loading state** — skeleton or spinner while data is fetching.
- **Empty state** — clear message when no results (e.g. “No students found”); optional CTA to create first item.
- **Pagination** — visible controls and optional page-size selector.
- **Filtering** — filters applied and reflected in the table; URL or state kept in sync if applicable.

### Forms

- **Validation messages** — show inline or below fields; clear and actionable.
- **Success feedback** — toast or inline message after successful create/update; then redirect or reset as appropriate.
- **Loading state** — disable submit button and show loading indicator during request.

---

## Deliverables

When generating a module, output:

1. **Files created/updated** — list paths (e.g. migration, model, controller, requests, resource, policy, routes, Vue pages and components).
2. **Routes added** — method, URI, name.
3. **Validation rules** — summary of required and optional fields per request.
4. **Edge cases** — e.g. unique constraints, soft deletes, workspace scoping, forbidden edits when status is X.
5. **Suggested tests** — e.g. feature tests for index/store/update/destroy and policy.

---

## Checklist (copy per module)

```
Backend:
- [ ] Migration
- [ ] Model (fillable, casts, relationships)
- [ ] Store + Update Form Requests
- [ ] Controller (index, store, show, update, destroy)
- [ ] API Resource(s)
- [ ] Policy
- [ ] Routes

Frontend:
- [ ] List page (search, filters, pagination, sorting, actions)
- [ ] Create page (validation, loading, error handling)
- [ ] Edit page (prefilled, validation, loading, error handling)
- [ ] (Optional) View page
- [ ] Table component (loading, empty, pagination, filtering)
- [ ] Form component (validation messages, success, loading)
```
