# Teachers Portal — Scope & Implementation Plan

> **Note:** The **Public Website Portal** is deferred. This document defines the **Teachers Portal** — the workspace teachers use after login to manage daily academic tasks. Focus: speed, clarity, and reducing manual work.

---

## Teachers Portal Scope

The portal includes these main modules:

### 1. Teacher Dashboard

Quick overview of teacher's work:

- Today's classes
- Assigned subjects
- Pending attendance tasks
- Pending exam marks entry
- Recent notices
- Upcoming events
- Quick links to common actions

### 2. My Profile

- View profile information
- View designation and department
- View assigned classes and subjects
- Change password
- Update limited editable information (if allowed)

### 3. Class Routine / Schedule

- View daily class routine
- View weekly schedule
- Filter by class/section
- See subject, room, and period timing

### 4. Attendance Management

Core module. Teachers can:

- Take student attendance by class and section
- Mark present, absent, late, leave
- Edit attendance within allowed rules
- View past attendance records
- Submit attendance for the day

**Optional later:** Period-wise attendance, bulk actions, attendance summary.

### 5. Subject & Class Assignment

Teachers can see:

- Which classes they teach
- Which sections they teach
- Which subjects they are assigned
- Academic year/session details

### 6. Student List

- View students in assigned classes
- Search students
- View basic student profiles
- View roll number and section
- View guardian contact info (if permitted)

### 7. Homework / Assignment Management

- Create homework / assignments
- Set title, description, subject, class, section, due date
- Upload attachment if needed
- View submitted assignments (if submission system exists)
- Update or delete own assignments

### 8. Lesson Plan

- Create lesson plans
- Link to class, subject, and date
- View weekly/monthly lesson plans
- Track completed vs pending lessons

### 9. Exam & Marks Entry

- View assigned exams
- Enter marks for students
- Update marks before final publish
- Save draft marks
- Submit final marks
- View grading rules if needed

**Optional:** Subject-wise tabulation preview, GPA/grade auto-calculation from backend rules.

### 10. Notices & Announcements

- View school notices
- View teacher-specific notices
- Read circulars and updates
- See important deadlines

### 11. Leave / Permission Requests

- Apply for leave
- Select leave type, dates, reason
- Upload supporting document if required
- View leave history
- Track approval status

### 12. Events / Calendar

- View academic calendar
- View holidays
- View exam schedules
- View school events
- View meeting schedules

### 13. Messaging / Communication

*(Can be added later depending on scope.)*

- Send messages to admin
- Send messages to assigned students/parents
- Receive system notifications
- Reminders for attendance, exams, meetings

### 14. Learning Materials / Resources

- Upload study materials
- Share PDFs, notes, documents
- Organize by class and subject
- Manage previously uploaded resources

### 15. Results / Academic Performance View

- View class performance summaries
- View student marks history for assigned subjects
- Identify weak students
- Review attendance and performance together

---

## Recommended Phase-wise Implementation

### Phase 1 — Core Foundation

1. Teacher authentication and portal access
2. Teacher dashboard
3. My profile
4. Class routine / schedule
5. Subject and class assignment

### Phase 2 — Daily Academic Operations

6. Attendance management  
7. Student list  
8. Homework / assignment management  
9. Lesson plan  

### Phase 3 — Examination Workflow

10. Exam list  
11. Marks entry  
12. Marks edit/update rules  
13. Performance summary  

### Phase 4 — Communication & Utility

14. Notices  
15. Events/calendar  
16. Leave requests  
17. Learning materials  

### Phase 5 — Advanced Features

18. Messaging  
19. Teacher analytics  
20. Downloadable reports  
21. Notification center  

---

## Access Rules

**A teacher should only access:**

- Their own profile
- Their assigned classes
- Their assigned subjects
- Their own attendance-related actions
- Their own homework / lesson plans / marks entry responsibilities

**A teacher should NOT access:**

- Full admin management
- Other teachers’ private data
- Unassigned classes or subjects
- System settings  

---

## Suggested Sidebar Menu (Teachers Portal)

- Dashboard
- My Profile
- My Classes
- Class Routine
- Attendance
- Students
- Homework / Assignments
- Lesson Plans
- Exams & Marks
- Notices
- Calendar
- Leave Request (Leave আবেদন)
- Learning Materials

---

## Best Next Step — Implementation Order

Implement the Teachers Portal in this order:

1. Teacher login and guard setup  
2. Teacher dashboard  
3. My classes / assigned subjects  
4. Class routine  
5. Attendance module  
6. Student list  
7. Homework / assignments  
8. Exams & marks entry  
9. Notices and leave  

A full step-by-step implementation plan with prompts (similar to the admin panel) can be created when starting Phase 1.
