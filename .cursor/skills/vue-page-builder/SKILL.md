---
name: vue-page-builder
description: Builds Vue 3 pages with Composition API and script setup. Use when adding or refactoring a full page (list, form, dashboard) in a Laravel + Vue SPA or Inertia app.
---

# Vue Page Builder

Create Vue 3 pages using Composition API and `<script setup>`.

## File Structure

```
pages/           # One page per file
components/      # Reusable UI for the page
composables/     # Shared reactive logic and state
services/        # API and external calls
```

Example for a feature:

- `pages/StudentsPage.vue` — main page
- `components/StudentTable.vue` — list/table
- `components/StudentForm.vue` — create/edit form
- `composables/useStudents.js` — state, loading, CRUD helpers
- `services/studentService.js` — HTTP calls to backend

## Component Rules

- Use `<script setup>` only.
- Keep components small; split when a file grows (e.g. > 300 lines).
- Avoid large templates; extract blocks into subcomponents.

## State Management

- Prefer **composables** and **reactive state** (ref/reactive).
- Move all API calls into **services**; call them from composables or pages.
- Do not put business logic in templates.

## UI States

Every page must handle:

| State    | Action |
|----------|--------|
| Loading  | Show spinner/skeleton; disable actions. |
| Empty    | Show empty message/CTA when no data. |
| Error    | Show error message; allow retry. |
| Success  | Show confirmation or redirect as needed. |

Handle these in the page or composable (e.g. `useStudents` exposes `loading`, `error`, `items`).

## Code Style

- Use **computed** for derived data; avoid complex expressions in templates.
- **Props**: define with type (and required/default) in script setup.
- **Emits**: declare with `defineEmits` and use for parent communication.
- Use stable keys in `v-for` (e.g. id, not index).
- Match existing project patterns (Tailwind, layout, existing components).

## Forms

- Bind fields to ref/reactive; submit via service in composable or page.
- Show validation errors from API response; disable submit while loading.
- Indicate success (message or redirect).

## Deliverables

When building a page, specify: files created/updated, main props and emits, state and API usage, and how loading, empty, error, and success states are handled.
