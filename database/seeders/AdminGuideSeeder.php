<?php

namespace Database\Seeders;

use App\Models\AdminGuide;
use App\Models\AdminGuideCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminGuideSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->where('user_type', 'admin')->value('id');

        $categories = [
            ['name' => 'Getting Started', 'slug' => 'getting-started', 'description' => 'Introduction and first steps', 'icon' => 'book', 'sort_order' => 0],
            ['name' => 'Dashboard', 'slug' => 'dashboard', 'description' => 'Dashboard and quick actions', 'icon' => 'layout-dashboard', 'sort_order' => 1],
            ['name' => 'Academic Management', 'slug' => 'academic-management', 'description' => 'Sessions, classes, sections, subjects', 'icon' => 'graduation', 'sort_order' => 2],
            ['name' => 'Student Management', 'slug' => 'student-management', 'description' => 'Students and academic assignment', 'icon' => 'users', 'sort_order' => 3],
            ['name' => 'Website Management', 'slug' => 'website-management', 'description' => 'Notices, events, pages, content', 'icon' => 'globe', 'sort_order' => 4],
            ['name' => 'User & Role Management', 'slug' => 'user-role-management', 'description' => 'Roles, permissions, and access', 'icon' => 'shield', 'sort_order' => 5],
            ['name' => 'Troubleshooting', 'slug' => 'troubleshooting', 'description' => 'Common issues and solutions', 'icon' => 'help-circle', 'sort_order' => 6],
        ];

        AdminGuideCategory::whereIn('slug', ['common-issues', 'user-management'])->update(['status' => 'inactive']);

        $created = [];
        foreach ($categories as $cat) {
            $created[$cat['slug']] = AdminGuideCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                array_merge($cat, ['status' => 'active'])
            );
        }

        $guidesContent = $this->getGuidesContent();
        foreach ($guidesContent as $catSlug => $items) {
            $category = $created[$catSlug] ?? null;
            if (! $category) {
                continue;
            }
            foreach ($items as $g) {
                AdminGuide::updateOrCreate(
                    ['slug' => $g['slug']],
                    [
                        'category_id' => $category->id,
                        'title' => $g['title'],
                        'short_description' => $g['short_description'],
                        'content' => $g['content'],
                        'status' => 'published',
                        'sort_order' => $g['sort_order'] ?? 0,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]
                );
            }
        }
    }

    private function getGuidesContent(): array
    {
        return [
            'getting-started' => [
                [
                    'title' => 'How to use the admin panel',
                    'slug' => 'how-to-use-the-admin-panel',
                    'short_description' => 'Overview of the admin interface and navigation.',
                    'sort_order' => 0,
                    'content' => "# How to use the admin panel\n\nWelcome to the school management admin panel. This guide explains the basics so you can work efficiently.\n\n## Logging in\n\n- Use the credentials provided by your administrator.\n- After login you will see the **Dashboard** and the **sidebar** on the left.\n\n## The sidebar\n\n- **Dashboard** – Your home screen with summary cards and quick actions.\n- **Academic** – Sessions, classes, sections, subjects, and teacher assignments.\n- **Staff Management** – Staff, teachers, departments, designations.\n- **Students** – Student records and academic assignment.\n- **Content** – Notices, news, events, galleries, downloads, pages, banners.\n- **Access Control** – Roles and permissions.\n- **Admin Guide** – Categories, guides, and this Help Center.\n\n## What you see depends on permissions\n\nYour **role** controls which menu items and actions you can use. If a menu item or button is missing, or you see \"Permission denied\", ask an administrator to assign the right permissions to your role.",
                ],
                [
                    'title' => 'Understanding the sidebar and navigation',
                    'slug' => 'understanding-the-sidebar-and-navigation',
                    'short_description' => 'How to move around the admin panel.',
                    'sort_order' => 1,
                    'content' => "# Understanding the sidebar and navigation\n\n## Sidebar structure\n\n- The **sidebar** is always visible on the left (on desktop). On mobile, use the menu button in the header to open it.\n- Items are grouped: for example, **Academic** contains **Sessions**, **Classes**, **Sections**, and **Subjects**.\n- Click a group name to expand it (if it has sub-items), then click a specific page to open it.\n\n## Header\n\n- The **header** shows the current page title and your name.\n- Use **Log out** when you finish your session.\n\n## Breadcrumbs and back\n\n- When you are on a list page (e.g. Students), use **Add** or a row action (Edit, View) to go to a form or detail page.\n- Use the browser back button or click the relevant sidebar item to return to a list.",
                ],
                [
                    'title' => 'Required fields and form validation',
                    'slug' => 'required-fields-and-form-validation',
                    'short_description' => 'How to fill forms correctly and fix validation errors.',
                    'sort_order' => 2,
                    'content' => "# Required fields and form validation\n\n## Required fields\n\n- Fields marked with a red asterisk (*) are **required**. You must fill them before saving.\n- Optional fields can be left blank unless the description says otherwise.\n\n## Validation errors\n\n- If you click **Save** and something is wrong, the form will show **validation errors** at the top or next to the field.\n- Common causes:\n  - **Required field empty** – Fill in every required field.\n  - **Invalid format** – For example, email must look like `user@school.com`; dates must be valid.\n  - **Duplicate value** – For example, two students cannot have the same admission number.\n  - **Invalid choice** – For example, the selected section must belong to the selected class.\n- Read the message, fix the indicated field, and try saving again.",
                ],
            ],
            'dashboard' => [
                [
                    'title' => 'How the dashboard works',
                    'slug' => 'how-the-dashboard-works',
                    'short_description' => 'What the dashboard shows and how to use it.',
                    'sort_order' => 0,
                    'content' => "# How the dashboard works\n\nThe dashboard is your home screen after login. It gives a quick overview of the system.\n\n## Purpose\n\n- See **key numbers** at a glance: total students, teachers, staff, current session, today's attendance, notices.\n- Spot what needs attention without opening each module.\n- Use **quick actions** to jump to common tasks (e.g. add student, take attendance).\n\n## When data is missing\n\n- If a card shows **0** or **—**, it usually means no data has been set up yet (e.g. no academic session, no students).\n- Create the required data in the relevant section (e.g. Academic → Sessions, then Students) and the dashboard will update.",
                ],
                [
                    'title' => 'What the dashboard cards mean',
                    'slug' => 'what-the-dashboard-cards-mean',
                    'short_description' => 'Explanation of each summary card.',
                    'sort_order' => 1,
                    'content' => "# What the dashboard cards mean\n\n## Summary cards\n\n- **Total Students** – Number of active student records.\n- **Total Teachers** – Number of teacher records.\n- **Total Staff** – Number of staff records.\n- **Active Session** – The academic session currently marked as \"current\" (used for attendance and reports).\n- **Today's Attendance** – How many students were marked present/absent for today.\n- **Published Notices** – Number of notices that are published and visible.\n\n## Academic and attendance overview\n\n- **Academic overview** – Current session and class summary.\n- **Attendance overview** – Count of students marked for today.\n\n## Recent activity\n\n- Shows recent actions in the system so you can see what changed.",
                ],
                [
                    'title' => 'How to use quick actions',
                    'slug' => 'how-to-use-quick-actions',
                    'short_description' => 'Shortcuts to common tasks from the dashboard.',
                    'sort_order' => 2,
                    'content' => "# How to use quick actions\n\n## What are quick actions?\n\n- **Quick actions** are buttons or links on the dashboard that take you directly to a specific task.\n- Examples: \"Add student\", \"Take attendance\", \"Create notice\".\n\n## How to use them\n\n- Click the action you need. You will be taken to the right page (e.g. student form or attendance screen).\n- Complete the form or steps as you normally would.\n- Quick actions are shortcuts; they do not skip permissions. If you do not have permission for that task, you will see an error.",
                ],
            ],
            'academic-management' => [
                [
                    'title' => 'How academic sessions work',
                    'slug' => 'how-academic-sessions-work',
                    'short_description' => 'Creating and managing academic years.',
                    'sort_order' => 0,
                    'content' => "# How academic sessions work\n\nAcademic sessions represent the **school year** (e.g. 2024–2025). Most reporting and daily work (attendance, exams) use the \"current\" session.\n\n## Creating a session\n\n1. Go to **Academic** → **Sessions**.\n2. Click **Add** or **Create session**.\n3. Enter **name** (e.g. 2024-2025), **start date**, and **end date**.\n4. Save.\n\n## Setting the current session\n\n- Only **one** session can be current at a time.\n- The current session is used for attendance, student assignment, and many reports.\n- Use the **Set current** action on the session that should be active for the year.\n\n## When to create a new session\n\n- At the start of a new academic year, create the new session and set it as current. The previous session remains in the system for historical data.",
                ],
                [
                    'title' => 'How classes work',
                    'slug' => 'how-classes-work',
                    'short_description' => 'Managing classes (e.g. Class 6, Class 7).',
                    'sort_order' => 1,
                    'content' => "# How classes work\n\nClasses are the grade levels in your school (e.g. Class 6, Class 7). They are used in routines, student assignment, and exams.\n\n## Creating a class\n\n1. Go to **Academic** → **Classes**.\n2. Click **Add** or **Create class**.\n3. Enter **name** (e.g. Class 9) and any **code** or **numeric level** if your system uses them.\n4. Save.\n\n## Important\n\n- Create classes **before** creating sections, because each section belongs to a class.\n- Create classes before assigning students to a class and section.",
                ],
                [
                    'title' => 'How sections work',
                    'slug' => 'how-sections-work',
                    'short_description' => 'Managing sections within a class.',
                    'sort_order' => 2,
                    'content' => "# How sections work\n\nSections are subdivisions of a class (e.g. Section A, Section B). Each section **belongs to one class**.\n\n## Creating a section\n\n1. Go to **Academic** → **Sections**.\n2. Click **Add** or **Create section**.\n3. Select the **class** and enter the **section name** (e.g. A).\n4. Save.\n\n## Use of sections\n\n- When you assign a student to a class for a session, you also choose a section.\n- Attendance, routines, and exams can be organised by class and section.\n- The section must belong to the class you selected; the form will not allow invalid combinations.",
                ],
                [
                    'title' => 'How subjects work',
                    'slug' => 'how-subjects-work',
                    'short_description' => 'Managing the subject catalog.',
                    'sort_order' => 3,
                    'content' => "# How subjects work\n\nSubjects are the courses you teach (e.g. Mathematics, English). They are used in routines, exams, and marks entry.\n\n## Creating a subject\n\n1. Go to **Academic** → **Subjects**.\n2. Click **Add** or **Create subject**.\n3. Enter **name**, and optionally **code**, **type** (e.g. general, elective), and **full marks / pass marks**.\n4. Save.\n\n## Using subjects\n\n- Assign subjects to teachers (Teacher Subject Assignments).\n- Use subjects when building class routines and when creating exams and entering marks.",
                ],
            ],
            'student-management' => [
                [
                    'title' => 'How to create a student',
                    'slug' => 'how-to-create-a-student',
                    'short_description' => 'Adding and editing student records.',
                    'sort_order' => 0,
                    'content' => "# How to create a student\n\n## Steps\n\n1. Go to **Students** in the sidebar.\n2. Click **Add student** (or similar).\n3. Fill in **required** fields:\n   - Admission number (unique)\n   - First name\n   - Last name\n   - Any other required fields shown on the form\n4. Fill optional fields (e.g. date of birth, gender, contact) if needed.\n5. Click **Save**.\n\n## After creating\n\n- You can open the student record to add or edit **academic assignment** (class and section for a session).\n- You can add guardians and update details later.",
                ],
                [
                    'title' => 'How student academic assignment works',
                    'slug' => 'how-student-academic-assignment-works',
                    'short_description' => 'Assigning students to class and section for a session.',
                    'sort_order' => 1,
                    'content' => "# How student academic assignment works\n\nAcademic assignment links a **student** to a **class** and **section** for a given **academic session**. This is used for attendance, exams, and reports.\n\n## Adding an assignment\n\n1. Open the **student** (e.g. from the Students list).\n2. Find **Academic assignment** or **Manage Academic Assignment**.\n3. Click **Add** or **Assign**.\n4. Choose **Academic session**, **Class**, and **Section**.\n5. Save.\n\n## Rules\n\n- The **section** must belong to the selected **class**.\n- A student should have only **one** assignment per session for a given class/section combination.\n- Use the **current** academic session for the ongoing year.",
                ],
                [
                    'title' => 'How to manage student status',
                    'slug' => 'how-to-manage-student-status',
                    'short_description' => 'Active, inactive, and other statuses.',
                    'sort_order' => 2,
                    'content' => "# How to manage student status\n\nStudent records can have a **status** (e.g. active, inactive) so you can keep historical data without showing left students in active lists.\n\n## Changing status\n\n1. Open the **student** record.\n2. Find the **status** field or **Edit** form.\n3. Set status to **active** or **inactive** (and any other values your system supports).\n4. Save.\n\n## Effect of status\n\n- **Active** students typically appear in attendance, exam, and report lists.\n- **Inactive** students are often hidden from daily operations but remain in the database for records.",
                ],
            ],
            'website-management' => [
                [
                    'title' => 'How to manage notices',
                    'slug' => 'how-to-manage-notices',
                    'short_description' => 'Creating and publishing notices.',
                    'sort_order' => 0,
                    'content' => "# How to manage notices\n\nNotices are announcements that can be shown on the school website or in the admin panel.\n\n## Creating a notice\n\n1. Go to **Content** → **Notices**.\n2. Click **Add notice**.\n3. Enter **title**, **content**, and select a **category**.\n4. Set **publish date** (and optionally **expiry date**).\n5. Add **attachments** if needed (e.g. PDF).\n6. Save as **draft** or **publish** when ready.\n\n## Publishing\n\n- **Draft** notices are not visible to the public. Use **Publish** when the notice is ready to show.\n- Set an **expiry date** so old notices are no longer shown.",
                ],
                [
                    'title' => 'How to manage events',
                    'slug' => 'how-to-manage-events',
                    'short_description' => 'Creating and publishing events.',
                    'sort_order' => 1,
                    'content' => "# How to manage events\n\nEvents are dated items (e.g. school day, parent meeting) that can be displayed on the website.\n\n## Creating an event\n\n1. Go to **Content** → **Events**.\n2. Click **Add event**.\n3. Enter **title**, **description**, **date** (and time if applicable), and **category**.\n4. Save as draft or publish.\n\n## Use\n\n- Events help staff and parents see what is coming up.\n- Publish when ready so they appear on the public site.",
                ],
                [
                    'title' => 'How to manage pages and content',
                    'slug' => 'how-to-manage-pages-and-content',
                    'short_description' => 'Static pages, banners, and homepage sections.',
                    'sort_order' => 2,
                    'content' => "# How to manage pages and content\n\n## Pages\n\n- **Pages** are static content (e.g. About us, Contact).\n- Create a page with a **title** and **slug** (used in the URL). Add body content and save. Publish when ready.\n\n## Banners and homepage sections\n\n- **Banners** – Images or slides on the homepage. Upload, set order, and toggle visibility.\n- **Homepage sections** – Blocks of content on the main page. Reorder and show/hide as needed.\n\n## Other content\n\n- **News**, **Galleries**, and **Downloads** are managed from their own sections under Content. Create, edit, and publish each type from the relevant list.",
                ],
            ],
            'user-role-management' => [
                [
                    'title' => 'How roles work',
                    'slug' => 'how-roles-work',
                    'short_description' => 'Understanding roles (e.g. School Admin, Content Manager).',
                    'sort_order' => 0,
                    'content' => "# How roles work\n\n**Roles** group a set of permissions. Each **user** is assigned one or more roles. What a user can do depends on the combined permissions of their roles.\n\n## Common roles\n\n- **Super Admin** – Full access.\n- **School Admin** – Full access for daily school operations.\n- **Content Manager** – Manages notices, events, pages, etc.\n- **Academic Manager** – Manages sessions, classes, students, attendance, exams.\n\n## Managing roles\n\n- Go to **Access Control** → **Roles** to see and edit roles.\n- Assign **permissions** to each role so that users with that role can perform the right actions.",
                ],
                [
                    'title' => 'How permissions affect access',
                    'slug' => 'how-permissions-affect-access',
                    'short_description' => 'What permissions do and why you might see \"Permission denied\".',
                    'sort_order' => 1,
                    'content' => "# How permissions affect access\n\n## What are permissions?\n\n- **Permissions** are fine-grained access rights (e.g. \"view students\", \"manage students\", \"view dashboard\").\n- They are attached to **roles**. A user gets permissions through the roles assigned to them.\n\n## What you see\n\n- **Sidebar** – You only see menu items for areas you have at least \"view\" permission for.\n- **Actions** – Buttons like Add, Edit, Delete appear only if you have the matching permission.\n- **403 Permission denied** – If you try to open a page or action you are not allowed, you will see this. Ask an administrator to assign the right role or permissions.",
                ],
            ],
            'troubleshooting' => [
                [
                    'title' => 'Why saving failed',
                    'slug' => 'why-saving-failed',
                    'short_description' => 'What to do when Save does not work.',
                    'sort_order' => 0,
                    'content' => "# Why saving failed\n\n## Validation errors\n\n- If the form shows **validation errors**, read the message. It usually says which field is wrong (e.g. \"The title field is required\", \"The slug has already been taken\").\n- Fix that field: fill required fields, use a unique value where required, and ensure dates and formats are valid. Then try saving again.\n\n## Permission denied\n\n- If you see **403** or \"Permission denied\", you do not have permission for that action. Ask an administrator to grant the right permission for your role.\n\n## Server or network errors\n\n- If you see **500** or \"Something went wrong\", try again in a moment. If it persists, report it to your administrator or technical support.",
                ],
                [
                    'title' => 'Why a page shows permission denied',
                    'slug' => 'why-permission-denied',
                    'short_description' => 'When you see 403 or cannot access a page.',
                    'sort_order' => 1,
                    'content' => "# Why a page shows permission denied\n\n## What it means\n\n- **403 Forbidden** or \"Permission denied\" means your user account does not have **permission** to view or perform that action.\n- The system is working; it is blocking access by design.\n\n## What to do\n\n1. **Do not share your password.**\n2. Contact an **administrator** and say which page or action you need (e.g. \"I need to manage students\").\n3. They can assign you a **role** that has the right **permissions**, or add the permission to your existing role.\n4. After your permissions are updated, log out and log in again, then try the page or action again.",
                ],
                [
                    'title' => 'Why records are not appearing in the list',
                    'slug' => 'why-records-not-appearing',
                    'short_description' => 'Empty lists and filters.',
                    'sort_order' => 2,
                    'content' => "# Why records are not appearing in the list\n\n## No data yet\n\n- If you have just started, **lists may be empty** until you create records. For example, create an **academic session** before creating classes; create **classes** before **sections**; create **students** before seeing them in lists.\n\n## Filters and search\n\n- **Filters** or **search** may be hiding results. Try clearing all filters and search, or set \"All\" or leave search blank, then apply again.\n\n## Status (draft vs published)\n\n- Some modules (e.g. notices, guides) have **draft** and **published** states. Lists may show only **published** items. If you created something as draft, it might not appear in the public or default list until you publish it.",
                ],
                [
                    'title' => 'Common validation errors and how to fix them',
                    'slug' => 'common-validation-errors',
                    'short_description' => 'Fixing required fields, dates, and duplicates.',
                    'sort_order' => 3,
                    'content' => "# Common validation errors and how to fix them\n\n## \"The [field] field is required\"\n\n- Fill in every field marked as required (*). Do not leave them blank.\n\n## \"The [field] has already been taken\"\n\n- That value must be **unique** (e.g. admission number, slug, email). Choose a different value or edit the existing record instead of creating a duplicate.\n\n## \"The end date must be after the start date\"\n\n- Check **date** fields. End date must be on or after the start date.\n\n## \"The selected [field] is invalid\"\n\n- The value you chose is not allowed. For example, the **section** must belong to the **class** you selected. Pick a valid option from the dropdown.\n\n## \"The [field] format is invalid\"\n\n- Use the correct format (e.g. email like `name@school.com`, slug with lowercase letters and hyphens only).",
                ],
            ],
        ];

        return $guides;
    }
}
