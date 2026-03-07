import { createRouter, createWebHistory } from 'vue-router';
import MainLayout from '../layouts/MainLayout.vue';
import { useAuthStore } from '../stores/authStore.js';

const routes = [
  {
    path: '/admin/login',
    name: 'login',
    component: () => import('../pages/LoginPage.vue'),
    meta: { title: 'Login', public: true },
  },
  {
    path: '/admin',
    component: MainLayout,
    children: [
      {
        path: '',
        redirect: '/admin/dashboard',
      },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('../pages/DashboardPage.vue'),
        meta: { title: 'Dashboard' },
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('../pages/UsersPage.vue'),
        meta: { title: 'Users' },
      },
      {
        path: 'academic',
        name: 'academic',
        component: () => import('../pages/AcademicPage.vue'),
        meta: { title: 'Academic Setup' },
      },
      {
        path: 'academic/sessions',
        name: 'academic-sessions',
        component: () => import('../pages/AcademicSessionsPage.vue'),
        meta: { title: 'Academic Sessions' },
      },
      {
        path: 'academic/classes',
        name: 'academic-classes',
        component: () => import('../pages/ClassesPage.vue'),
        meta: { title: 'Classes' },
      },
      {
        path: 'academic/sections',
        name: 'academic-sections',
        component: () => import('../pages/SectionsPage.vue'),
        meta: { title: 'Sections' },
      },
      {
        path: 'academic/subjects',
        name: 'academic-subjects',
        component: () => import('../pages/SubjectsPage.vue'),
        meta: { title: 'Subjects' },
      },
      {
        path: 'academic/teacher-subject-assignments',
        name: 'teacher-subject-assignments',
        component: () => import('../pages/TeacherSubjectAssignmentsPage.vue'),
        meta: { title: 'Teacher Subject Assignments' },
      },
      {
        path: 'staff/staffs',
        name: 'staff-staffs',
        component: () => import('../pages/StaffsPage.vue'),
        meta: { title: 'Staff' },
      },
      {
        path: 'staff/teachers',
        name: 'staff-teachers',
        component: () => import('../pages/TeachersPage.vue'),
        meta: { title: 'Teachers' },
      },
      {
        path: 'staff/departments',
        name: 'staff-departments',
        component: () => import('../pages/DepartmentsPage.vue'),
        meta: { title: 'Departments' },
      },
      {
        path: 'staff/designations',
        name: 'staff-designations',
        component: () => import('../pages/DesignationsPage.vue'),
        meta: { title: 'Designations' },
      },
      {
        path: 'students',
        name: 'students',
        component: () => import('../pages/StudentsPage.vue'),
        meta: { title: 'Students' },
      },
      {
        path: 'students/:id',
        name: 'student-details',
        component: () => import('../pages/StudentDetailsPage.vue'),
        meta: { title: 'Student details' },
      },
      {
        path: 'attendance',
        name: 'attendance',
        component: () => import('../pages/AttendancePage.vue'),
        meta: { title: 'Attendance' },
      },
      {
        path: 'attendance/take',
        name: 'take-attendance',
        component: () => import('../pages/TakeAttendancePage.vue'),
        meta: { title: 'Take Attendance' },
      },
      {
        path: 'attendance/:id',
        name: 'attendance-details',
        component: () => import('../pages/AttendanceDetailsPage.vue'),
        meta: { title: 'Attendance details' },
      },
      {
        path: 'routines',
        name: 'routines',
        component: () => import('../pages/ClassRoutinesPage.vue'),
        meta: { title: 'Class Routines' },
      },
      {
        path: 'routines/create',
        name: 'routines-create',
        component: () => import('../pages/ClassRoutineBuilderPage.vue'),
        meta: { title: 'Create Routine' },
      },
      {
        path: 'routines/:id',
        name: 'routine-details',
        component: () => import('../pages/ClassRoutineDetailsPage.vue'),
        meta: { title: 'Routine details' },
      },
      {
        path: 'routines/:id/edit',
        name: 'routine-edit',
        component: () => import('../pages/ClassRoutineBuilderPage.vue'),
        meta: { title: 'Edit Routine' },
      },
      {
        path: 'exams/types',
        name: 'exam-types',
        component: () => import('../pages/ExamTypesPage.vue'),
        meta: { title: 'Exam Types' },
      },
      {
        path: 'exams',
        name: 'exams',
        component: () => import('../pages/ExamsPage.vue'),
        meta: { title: 'Exams' },
      },
      {
        path: 'exams/create',
        name: 'exams-create',
        component: () => import('../pages/ExamBuilderPage.vue'),
        meta: { title: 'Create Exam' },
      },
      {
        path: 'exams/:id',
        name: 'exam-details',
        component: () => import('../pages/ExamDetailsPage.vue'),
        meta: { title: 'Exam details' },
      },
      {
        path: 'exams/:id/edit',
        name: 'exam-edit',
        component: () => import('../pages/ExamBuilderPage.vue'),
        meta: { title: 'Edit Exam' },
      },
      {
        path: 'marks-entry',
        name: 'marks-entry',
        component: () => import('../pages/MarksEntryPage.vue'),
        meta: { title: 'Marks Entry' },
      },
      {
        path: 'marks-entry/create',
        name: 'marks-entry-create',
        component: () => import('../pages/EnterMarksPage.vue'),
        meta: { title: 'Enter Marks' },
      },
      {
        path: 'marks-entry/detail',
        name: 'marks-entry-detail',
        component: () => import('../pages/MarksEntryDetailsPage.vue'),
        meta: { title: 'Marks Entry Review' },
      },
      {
        path: 'results/grade-scales',
        name: 'grade-scales',
        component: () => import('../pages/GradeScalesPage.vue'),
        meta: { title: 'Grade Scales' },
      },
      {
        path: 'results',
        name: 'results',
        component: () => import('../pages/ResultsPage.vue'),
        meta: { title: 'Results' },
      },
      {
        path: 'results/generate',
        name: 'results-generate',
        component: () => import('../pages/GenerateResultsPage.vue'),
        meta: { title: 'Generate Results' },
      },
      {
        path: 'results/:id',
        name: 'result-details',
        component: () => import('../pages/ResultDetailsPage.vue'),
        meta: { title: 'Result details' },
      },
      {
        path: 'settings',
        name: 'settings',
        redirect: '/admin/settings/school-profile',
      },
      {
        path: 'settings/school-profile',
        name: 'school-profile',
        component: () => import('../pages/SchoolProfilePage.vue'),
        meta: { title: 'School Profile' },
      },
      {
        path: 'settings/system',
        name: 'system-settings',
        component: () => import('../pages/SystemSettingsPage.vue'),
        meta: { title: 'System Settings' },
      },
      {
        path: 'roles',
        name: 'roles',
        component: () => import('../pages/RolesPage.vue'),
        meta: { title: 'Roles' },
      },
      {
        path: 'permissions',
        name: 'permissions',
        component: () => import('../pages/PermissionsPage.vue'),
        meta: { title: 'Permissions' },
      },
      {
        path: ':pathMatch(.*)*',
        name: 'not-found',
        component: () => import('../pages/NotFoundPage.vue'),
        meta: { title: 'Page not found' },
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to) => {
  const authStore = useAuthStore();
  await authStore.initAuth();

  const isPublic = to.meta.public === true;
  const isAuthenticated = !!authStore.user;

  if (isPublic && isAuthenticated) {
    return { path: '/admin/dashboard' };
  }
  if (!isPublic && !isAuthenticated) {
    return { path: '/admin/login', query: { redirect: to.fullPath } };
  }
  return true;
});

export default router;
