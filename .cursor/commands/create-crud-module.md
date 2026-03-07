# create-crud-module

Generate a full CRUD module for the admin panel.

The module name will be provided when running the command (e.g. `/create-crud-module students`).

---

# Goal

Create a complete CRUD flow for the given resource:

1. **Backend:** Migration, Model, Controller, Form Requests (Store/Update), validation, list/show/store/update (and optionally destroy). Follow Laravel 12 + project architecture (services where appropriate, thin controllers).
2. **API routes:** Register list, create, show, update (and optionally delete) under `/api/admin/{resource}`.
3. **Frontend:** List page with table, Add button, create/edit modal or form page, service for API calls. Use shared components (SearchableSelect, DatePicker, etc.) and admin UI rules.
4. **Filters:** Add filter bar (search, status if applicable) and backend filter support.
5. **Required labels:** Mark required form fields with red `*` beside labels.

---

# Step 1 — Locate or Infer Naming

Use the module name from the command to determine:

- **Resource name** (singular, e.g. student, subject)
- **PascalCase** for classes (e.g. Student, Subject)
- **Routes** (e.g. students, subjects)
- **Frontend:** `{Resource}Page.vue`, `{Resource}Form.vue`, `{resource}Service.js`

---

# Step 2 — Backend

- Create migration for the table (include `school_id` if multi-tenant).
- Create Model with fillable, casts, relationships.
- Create Controller with index (with filters), store, show, update (and destroy if needed).
- Create Store and Update Form Requests with validation and authorization.
- Register routes in `routes/api.php` (before any `{resource}/{id}` route).

---

# Step 3 — Frontend

- **Service:** `getList(params)`, `getOne(id)`, `create(payload)`, `update(id, payload)`, optionally `remove(id)`.
- **List page:** Filter bar (search, status), table, Add button, Edit/Delete actions, empty state.
- **Form:** Modal or page with required field labels (red `*`), validation, success/error toasts. Use shared components.

---

# Step 4 — Consistency

- Follow existing modules (e.g. Classes, Sections, Academic Sessions) for structure and styling.
- Use toast for success/error; use confirmation modal for destructive actions.
- No `alert()`, `confirm()`, or `prompt()`.

---

# Definition of Done

- Resource is listable, creatable, editable (and deletable if specified).
- Backend validates and authorizes; frontend shows filters and required labels.
- UI matches admin dashboard design.
