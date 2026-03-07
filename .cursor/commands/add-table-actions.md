# add-table-actions

Add or standardize **table row actions** (e.g. Edit, Delete, Set current, View) to the specified module list page.

The module name will be provided when running the command (e.g. `/add-table-actions sessions`).

---

# Goal

1. Ensure the list/table page has an **Actions** column (or equivalent).
2. Add or align **row actions** such as: Edit, Delete, View, Set current, etc., depending on the module.
3. Use **links or buttons** (not native dialogs). Destructive actions must use the **shared confirmation modal**; success/error must use **toast**.
4. Follow the project's feedback and confirmation rules (no `alert()`, `confirm()`, `prompt()`).

---

# Step 1 — Locate List Page

Find: `resources/js/admin/pages/{Module}Page.vue` (e.g. AcademicSessionsPage.vue, ClassesPage.vue).

Identify the table and whether an Actions column exists.

---

# Step 2 — Define Actions per Module

- **Edit:** Opens create/edit form (modal or route) with the row’s data. Common for all CRUD modules.
- **Delete:** Shows confirmation modal (“Confirm deletion”, “Delete”), then calls delete API, then success toast and list refresh.
- **View:** Navigate to detail page if the module has one.
- **Set current / Publish / Archive:** If the module has such actions, use confirmation modal then API then toast.

Only add actions that the backend supports and the user requested.

---

# Step 3 — Implementation

- Add or update the Actions column with buttons/links.
- Use consistent styling (e.g. text links or icon buttons) matching other admin list pages.
- Wire each action: open modal, navigate, or call API with confirmation/toast as per project rules.

---

# Step 4 — Backend

If an action requires an endpoint (e.g. set-current, delete), ensure the controller has that method and the route is registered. Do not add backend actions that are out of scope for this command unless necessary.

---

# Definition of Done

- Table has clear row actions.
- Destructive actions use confirmation modal; success/error use toast.
- UI matches admin dashboard design.
