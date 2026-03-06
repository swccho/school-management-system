---
name: school-domain-skill
description: Understand school management domain entities (AcademicSession, Class, Section, Student, Attendance, Exam, Result, routines, notices). Use when implementing or discussing school-related features, data models, migrations, or business rules.
---

# School Domain Knowledge

## Academic Structure

| Entity | Purpose |
|--------|--------|
| **AcademicSession** | Year/term scope (e.g. 2024–2025). |
| **Class** | Grade level (e.g. Class 5, Class 10). |
| **Section** | Subdivision of a class (e.g. 5-A, 5-B). |
| **Subject** | Course/subject taught. |
| **TeacherAssignment** | Links teacher(s) to class/section/subject. |

## Student Management

| Entity | Purpose |
|--------|--------|
| **Student** | Enrolled learner; must belong to class, section, and academic session. |
| **Guardian** | Parent/guardian; linked to student(s). |
| **Admission** | Admission record / enrollment. |
| **StudentPromotion** | Movement to next class/session. |

## Attendance

| Entity | Purpose |
|--------|--------|
| **Attendance** | Container (e.g. date, class/section). |
| **AttendanceRecord** | Per-student, per-day (or per-period) status (present/absent/late). |

Attendance is recorded **daily**, **per class/section**, **per student**.

## Exams

| Entity | Purpose |
|--------|--------|
| **Exam** | Exam type/name (e.g. Mid-term, Final). |
| **ExamSchedule** | When and for which class/subject. |
| **Marks** | Marks per student, per subject/exam. |
| **Result** | Aggregated result; supports GPA and marksheets. |
| **GradeRule** | Mapping from marks/score to grade or GPA. |

Results must support: **GPA calculation**, **grade rules**, **marksheets**.

## Scheduling

| Entity | Purpose |
|--------|--------|
| **ClassRoutine** | Timetable: subjects, slots, rooms, teachers. |
| **ExamRoutine** | Exam dates/times per class/subject. |

## Content

| Entity | Purpose |
|--------|--------|
| **Notice** | Announcements. |
| **Event** | School events. |
| **Gallery** | Image/media gallery. |
| **Download** | Downloadable resources. |

## Users (Roles)

- **Admin** – full system access.
- **Teacher** – teaching assignments, marks, attendance (as assigned).
- **Student** – own profile, results, attendance.
- **Guardian** – linked students’ info (attendance, results).

## Domain Rules (Summary)

1. **Student placement**  
   Every student must belong to: **class**, **section**, **academic session**.

2. **Attendance**  
   Recorded **daily**, **per class/section**, **per student**; use `Attendance` + `AttendanceRecord` (or equivalent).

3. **Results**  
   - Compute GPA from marks and **GradeRule**.  
   - Apply grade rules consistently.  
   - Generate **marksheets** (per student, per exam/session).

When implementing: scope data by workspace/tenant, enforce these rules in services/policies, and validate class/section/session consistency on create/update.
