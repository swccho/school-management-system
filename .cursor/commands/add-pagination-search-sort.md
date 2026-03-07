# add-pagination-search-sort

Add **pagination**, **search**, and **sorting** to the specified module list API and list page.

The module name will be provided when running the command (e.g. `/add-pagination-search-sort students`).

---

# Goal

1. **Backend:** List API supports `page`, `per_page` (or `limit`), `search` (or existing filter), and `sort`/`order` (e.g. sort by name, date, with asc/desc).
2. **Frontend:** List page shows pagination controls (prev/next or page numbers), uses search/filters, and allows sortable column headers or a sort dropdown.
3. Preserve existing filters; add pagination and sort alongside them.
4. Follow the project’s API and UI conventions.

---

# Step 1 — Backend

- **Controller:** In the list (index) method, accept query params: `page`, `per_page`, `search` (if not already), `sort_by`, `sort_order` (e.g. asc/desc).
- Use `->paginate($request->input('per_page', 15))` (or project default).
- Apply `->orderBy($sortBy, $sortOrder)` with whitelisted columns (e.g. name, code, created_at) and default (e.g. created_at desc).
- Return paginated response (Laravel default: `data`, `meta` with current_page, last_page, per_page, total, etc.).

---

# Step 2 — Frontend Service

- Update list API call to pass `page`, `per_page`, `sort_by`, `sort_order` (and existing filter params).
- Handle response shape: list in `data`, pagination info in `meta` or equivalent.

---

# Step 3 — Frontend List Page

- Add **pagination UI** (e.g. “Previous”, “Next”, or page numbers) below the table. Disable when on first/last page.
- Add **sort** UI: clickable column headers or a sort dropdown. Update `sort_by` and `sort_order` and refetch.
- Keep existing **search/filter** bar; ensure applying filters resets to page 1 and uses backend search/sort.

---

# Step 4 — Consistency

- Match pagination and sort patterns used in other admin modules if any.
- Preserve existing filter behavior; only add pagination and sort.

---

# Definition of Done

- List API returns paginated, sortable results.
- List page has working pagination and sort (and existing search/filters).
- UI matches admin dashboard design.
