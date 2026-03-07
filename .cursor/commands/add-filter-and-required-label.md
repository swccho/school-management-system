# add-filter-and-required-label

Add **filter support** and **required field labels** to the specified module.

This command standardizes filtering UI and required field labeling across all modules.

The module name will be provided when running the command.

Example usage:

```
/add-filter-and-required-label academic-sessions
/add-filter-and-required-label classes
/add-filter-and-required-label students
```

---

# Goal

For the given module:

1. Add a **filter bar** to the list page.
2. Add **backend filtering support** to the list API.
3. Mark **required form fields with a red `*`** beside labels.
4. Follow the project's UI rules and reusable components.

---

# Step 1 — Locate Module

Identify the module provided in the command.

Typical locations:

**Frontend:**

```
resources/js/admin/pages/{Module}Page.vue
resources/js/admin/components/{Module}Form.vue
resources/js/admin/services/{module}Service.js
```

**Backend:**

```
app/Http/Controllers/Admin/{Module}Controller.php
```

---

# Step 2 — Add Filter Bar

Add a filter section above the table.

**Typical filters:**

### Search

Search by:

- name
- code
- title (if applicable)

Example placeholder: `Search...`

### Status Filter

Dropdown options: **All**, **Active**, **Inactive**

Use global `SearchableSelect` if needed.

### Date Range Filters (if module has dates)

Use global `DatePicker`.

Examples: **Start Date From**, **Start Date To**, **Created Date From**, **Created Date To**

### Filter Buttons

Add: **Apply Filters**, **Reset**

---

# Step 3 — Backend Filters

Update the list API.

Example endpoint: `GET /api/admin/{module}`

Add query parameter support for: `search`, `status`, `date_from`, `date_to`

Example filtering logic:

```php
->when($request->search, function ($query) use ($request) {
    $query->where(function ($q) use ($request) {
        $q->where('name', 'like', "%{$request->search}%")
          ->orWhere('code', 'like', "%{$request->search}%");
    });
})

->when($request->status, fn ($q) =>
    $q->where('status', $request->status)
)

->when($request->date_from, fn ($q) =>
    $q->whereDate('created_at', '>=', $request->date_from)
)

->when($request->date_to, fn ($q) =>
    $q->whereDate('created_at', '<=', $request->date_to)
)
```

---

# Step 4 — Required Field Labels

Update all forms for the module.

Every required field label must show: **Label Name \*** where `*` is **red** and placed directly after the label text.

Example:

```html
<label class="form-label">
  Name <span class="text-red-500">*</span>
</label>
```

---

# Step 5 — Determine Required Fields

Required fields must match backend validation rules.

Example Laravel validation:

```php
'name' => ['required', 'string'],
'code' => ['required', 'unique:classes'],
```

Frontend must reflect this with the red `*`. Do not mark optional fields.

---

# Step 6 — UI Consistency Rules

**Filters must:**

- appear above the table
- use the admin dashboard design
- use spacing consistent with other modules
- be responsive
- wrap on smaller screens

**Required labels must:**

- use the same red color everywhere (`text-red-500`)
- appear only once beside the label
- not be repeated inside placeholders

---

# Step 7 — Reset Behavior

Reset must:

- clear search
- clear filters
- reload the list API
- restore default results

---

# Step 8 — Expected Output

At the end provide:

1. module updated
2. frontend files modified
3. backend files modified
4. filters added
5. required fields marked
6. example API request (e.g. `/api/admin/classes?search=class1&status=active`)

---

# Definition of Done

This command is complete when:

- module list page contains filter bar
- search filter works
- status filter works
- date filters work (if applicable)
- reset works
- backend API supports filters
- required fields show red `*`
- UI matches admin dashboard design
