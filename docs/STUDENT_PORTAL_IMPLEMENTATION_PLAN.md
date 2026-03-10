# Student Portal — Implementation Plan

## Goal

The **Student Portal** allows students to:

- View their academic information
- Check assignments
- View results
- Download materials
- Track attendance
- Communicate with teachers
- Manage their profile

The student portal is **read-heavy** (most data comes from admin/teachers).

---

## Student Portal — Main Modules

### 1. Student Authentication

Students must be able to log in securely.

**Features:**

- Student login
- Forgot password
- Change password
- Session management

**Login options:**

- Student ID + Password
- Email + Password

---

### 2. Student Dashboard

This is the **main page after login**.

**Dashboard should show:**

- Student Profile summary
- Class & Section
- Today's class schedule
- Latest assignments
- Attendance summary
- Recent announcements
- Upcoming exams

**Widgets:**

- Attendance percentage
- Pending assignments
- Latest results
- Teacher announcements

---

### 3. My Profile

Student can view their information.

**Fields:**

- Name
- Student ID
- Roll number
- Class
- Section
- Group
- Blood group
- Date of birth
- Guardian information
- Contact info
- Address
- Profile photo

**Actions:**

- Update profile photo
- Update contact info
- Change password

(Some fields read-only from admin)

---

### 4. Class Routine

Students can see:

- Weekly class routine
- Subject
- Teacher
- Room
- Time

**View types:**

- Daily view
- Weekly timetable

---

### 5. Assignments

Students can:

- View assignments
- Download assignment files
- Submit assignments
- Check submission status

**Assignment info:**

- Subject
- Teacher
- Deadline
- Instructions
- Attachments

**Student actions:**

- Upload submission
- View grade/feedback

---

### 6. Results / Academic Records

Students can view:

- Exam results
- Subject marks
- Grade
- GPA
- Class position (optional)

**Views:**

- Term results
- Subject results
- Academic transcript

**Export:**

- Download result PDF

---

### 7. Attendance

Students can view attendance statistics.

**Features:**

- Monthly attendance
- Subject attendance
- Attendance percentage
- Absent days

**Visual:**

- Charts / graphs

---

### 8. Study Materials

Teachers/admin upload learning materials.

Students can:

- Browse materials by subject
- Download files

**Files:**

- PDF
- Notes
- Slides
- Videos
- Links

---

### 9. Announcements / Notices

Students can view school announcements.

**Examples:**

- Holidays
- Exam schedules
- Events
- School notices

**Features:**

- Notification list
- Mark as read

---

### 10. Exam Schedule

Students can view upcoming exams.

**Fields:**

- Exam name
- Subject
- Date
- Time
- Room

**Views:**

- Exam timetable

---

### 11. Messaging (Optional Phase)

Students can communicate with teachers.

**Features:**

- Student → Teacher messages
- Teacher replies
- Notifications

---

### 12. Fee Information (Optional)

Students can check:

- Fee history
- Pending fees
- Payment status

---

## Student Portal UI Layout

Suggested layout:

```
Student Portal

Sidebar
-------
Dashboard
My Profile
Class Routine
Assignments
Results
Attendance
Study Materials
Announcements
Exam Schedule
Messages

Topbar
------
Notifications
Profile Menu
Logout
```

---

## Student Portal Tech Structure

### Backend (Laravel)

**Controllers:**

- StudentDashboardController
- StudentProfileController
- StudentAssignmentController
- StudentResultController
- StudentAttendanceController
- StudentMaterialController
- StudentNoticeController
- StudentExamController

### Frontend (Vue + shadcn style like Admin)

**Pages:** `resources/student/pages/`

- Dashboard.vue
- Profile.vue
- Assignments.vue
- AssignmentView.vue
- SubmitAssignment.vue
- Results.vue
- Attendance.vue
- Routine.vue
- Materials.vue
- Notices.vue
- Exams.vue

---

## Student Portal — Total Implementation Steps (15)

To build it properly we should divide it into **15 steps**.

| Step | Description |
|------|-------------|
| 1 | Student authentication |
| 2 | Student layout |
| 3 | Student dashboard |
| 4 | Student profile |
| 5 | Class routine |
| 6 | Assignments list |
| 7 | Assignment submission |
| 8 | Assignment grading view |
| 9 | Results module |
| 10 | Attendance module |
| 11 | Study materials |
| 12 | Notices |
| 13 | Exam schedule |
| 14 | Notifications |
| 15 | UI polish |

---

## Cursor Prompts (Copy-Paste Format)

When implementing, use step-by-step prompts like:

```
Step 1 / 15 — Student authentication
Step 2 / 15 — Student layout
Step 3 / 15 — Student dashboard
...
Step 15 / 15 — UI polish
```

---

## Result

After implementation, the system will have:

- Admin Portal
- Teachers Portal
- **Student Portal**
- Public Website (remaining)
