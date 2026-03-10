# Test Suite Summary

## Test Files Added

### Infrastructure
- `tests/TestCase.php` – base test case (unchanged)
- `tests/Feature/Concerns/ActsAsAdmin.php` – trait: `RefreshDatabase`, `createSchool()`, `createAdminUser(permissions, actAs)`, `actingAsAdminWithPermission(slugs)`
- `tests/Feature/Admin/AdminApiTestCase.php` – base class for admin API tests (uses `ActsAsAdmin`)

### Factories (`database/factories/`)
- `SchoolFactory.php`
- `RoleFactory.php`
- `PermissionFactory.php`
- `AcademicSessionFactory.php` (with `current()`, `forSchool()`)
- `SchoolClassFactory.php` (with `forSchool()`)
- `SectionFactory.php` (with `forClass()`)
- `StudentFactory.php` (with `forSchool()`)
- `StudentAcademicAssignmentFactory.php`

`UserFactory.php` was updated to include `school_id`, `user_type`, `status`, `phone`, `username`, `avatar`, `last_login_at`.

### Feature Tests
- `tests/Feature/Auth/AdminAuthTest.php` – login (valid/invalid/non-admin), me (401/200), dashboard (401/403/200)
- `tests/Feature/Admin/DashboardTest.php` – dashboard structure, summary cards reflect student count
- `tests/Feature/Admin/StudentAcademicAssignmentTest.php` – index filter by student_id, store (success/duplicate 422), show, update; permission checks
- `tests/Feature/Admin/AcademicSessionTest.php` – index/store/show/update, auth
- `tests/Feature/Admin/SchoolClassTest.php` – index/store/show/update, auth
- `tests/Feature/Admin/SectionTest.php` – index/store/show/update, auth
- `tests/Feature/Admin/SubjectTest.php` – index/store/show/update, auth
- `tests/Feature/Admin/StudentTest.php` – index/store/show/update, show includes `current_academic_assignment`
- `tests/Feature/Admin/UserTest.php` – index/show/store, auth
- `tests/Feature/Admin/RoleTest.php` – index/store/show, auth
- `tests/Feature/Admin/NoticeTest.php` – index/store/show, auth
- `tests/Feature/Admin/ValidationTest.php` – SAA required fields & section-for-class, academic session name/end_date, student first_name

### Unit Tests
- `tests/Unit/Services/AdminDashboardServiceTest.php` – `getDashboardData()` structure, empty DB zero counts, student count and recent students, current session

## Behaviors Covered

- **Auth:** Login success/failure, non-admin 403, me with/without auth, dashboard 401/403/200
- **Dashboard:** Response shape, summary_cards and recent_activity, integration with student count
- **Student Academic Assignment:** Index with `student_id` filter, store (valid + duplicate validation), show, update, permissions (view-attendance, manage-attendance)
- **Academic Session, School Class, Section, Subject, Student:** Index (403 without permission, 200 with), store, show, update where applicable
- **User, Role, Notice:** Index, store, show as applicable
- **Validation:** SAA required fields and section-in-class rule, academic session name and end_date, student first_name
- **AdminDashboardService:** Full response structure, empty DB, student count and recent activity, current session

## Gaps / Higher-Risk Areas

- **Not covered:** Delete endpoints for most resources, publish/unpublish flows, setCurrent session, syncPermissions, file uploads, MarksEntry/Result generation, AttendanceSession store with records, Teacher/Staff (no factories), Exam/NoticeCategory factories, pagination
- **Risky without more tests:** Complex flows (marks entry, result generation), file upload validation, permission sync and role-permission matrix

## Running Tests

Tests use **MySQL** with a dedicated test database (see `phpunit.xml`: `DB_CONNECTION=mysql`, `DB_DATABASE=school_management_test`). The test suite creates `school_management_test` automatically if it does not exist (see `tests/TestCase.php`). Your MySQL user must have `CREATE DATABASE` permission; otherwise create the database manually:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS school_management_test;"
```

From the project root:

```bash
# Full suite
php artisan test

# Or
./vendor/bin/phpunit

# Feature only
php artisan test --testsuite=Feature

# Unit only
php artisan test --testsuite=Unit

# Single file
php artisan test tests/Feature/Admin/DashboardTest.php
```

If you prefer SQLite in-memory (faster, no DB setup), enable the PHP PDO SQLite extension in `php.ini` (`extension=pdo_sqlite`) and in `phpunit.xml` set `DB_CONNECTION` to `sqlite` and `DB_DATABASE` to `:memory:`.
