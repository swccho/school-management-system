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
        meta: { title: 'Dashboard', helpSlug: 'how-the-dashboard-works' },
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('../pages/UsersPage.vue'),
        meta: { title: 'Users', helpSlug: 'how-roles-work' },
      },
      {
        path: 'users/create',
        name: 'users-create',
        component: () => import('../pages/CreateUserPage.vue'),
        meta: { title: 'Add User' },
      },
      {
        path: 'users/:id',
        name: 'user-details',
        component: () => import('../pages/UserDetailsPage.vue'),
        meta: { title: 'User details' },
      },
      {
        path: 'users/:id/edit',
        name: 'users-edit',
        component: () => import('../pages/EditUserPage.vue'),
        meta: { title: 'Edit User' },
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
        meta: { title: 'Academic Sessions', helpSlug: 'how-academic-sessions-work' },
      },
      {
        path: 'academic/classes',
        name: 'academic-classes',
        component: () => import('../pages/ClassesPage.vue'),
        meta: { title: 'Classes', helpSlug: 'how-classes-work' },
      },
      {
        path: 'academic/sections',
        name: 'academic-sections',
        component: () => import('../pages/SectionsPage.vue'),
        meta: { title: 'Sections', helpSlug: 'how-sections-work' },
      },
      {
        path: 'academic/subjects',
        name: 'academic-subjects',
        component: () => import('../pages/SubjectsPage.vue'),
        meta: { title: 'Subjects', helpSlug: 'how-subjects-work' },
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
        meta: { title: 'Students', helpSlug: 'how-to-create-a-student' },
      },
      {
        path: 'students/:id/academic-assignment',
        name: 'student-academic-assignment',
        component: () => import('../pages/StudentAcademicAssignmentPage.vue'),
        meta: { title: 'Academic assignment', helpSlug: 'how-student-academic-assignment-works' },
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
        path: 'notices',
        name: 'notices',
        component: () => import('../pages/NoticesPage.vue'),
        meta: { title: 'Notices', helpSlug: 'how-to-manage-notices' },
      },
      {
        path: 'news-posts',
        name: 'news-posts',
        component: () => import('../pages/NewsPostsPage.vue'),
        meta: { title: 'News' },
      },
      {
        path: 'events',
        name: 'events',
        component: () => import('../pages/EventsPage.vue'),
        meta: { title: 'Events', helpSlug: 'how-to-manage-events' },
      },
      {
        path: 'galleries',
        name: 'galleries',
        component: () => import('../pages/GalleriesPage.vue'),
        meta: { title: 'Galleries' },
      },
      {
        path: 'galleries/:id',
        name: 'gallery-details',
        component: () => import('../pages/GalleryDetailsPage.vue'),
        meta: { title: 'Gallery details' },
      },
      {
        path: 'downloads',
        name: 'downloads',
        component: () => import('../pages/DownloadsPage.vue'),
        meta: { title: 'Downloads' },
      },
      {
        path: 'pages',
        name: 'pages',
        component: () => import('../pages/PagesPage.vue'),
        meta: { title: 'Pages', helpSlug: 'how-to-manage-pages-and-content' },
      },
      {
        path: 'banners',
        name: 'banners',
        component: () => import('../pages/BannersPage.vue'),
        meta: { title: 'Banners' },
      },
      {
        path: 'homepage-sections',
        name: 'homepage-sections',
        component: () => import('../pages/HomepageSectionsPage.vue'),
        meta: { title: 'Homepage Sections' },
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
        meta: { title: 'Roles', helpSlug: 'how-roles-work' },
      },
      {
        path: 'roles/create',
        name: 'roles-create',
        component: () => import('../pages/CreateRolePage.vue'),
        meta: { title: 'Add Role' },
      },
      {
        path: 'roles/:id/edit',
        name: 'roles-edit',
        component: () => import('../pages/EditRolePage.vue'),
        meta: { title: 'Edit Role' },
      },
      {
        path: 'roles/:id/permissions',
        name: 'role-permissions',
        component: () => import('../pages/RolePermissionsPage.vue'),
        meta: { title: 'Manage Permissions' },
      },
      {
        path: 'roles/:id',
        name: 'role-details',
        component: () => import('../pages/RoleDetailsPage.vue'),
        meta: { title: 'Role details' },
      },
      {
        path: 'permissions',
        name: 'permissions',
        component: () => import('../pages/PermissionsPage.vue'),
        meta: { title: 'Permissions', helpSlug: 'how-permissions-affect-access' },
      },
      {
        path: 'permissions/create',
        name: 'permissions-create',
        component: () => import('../pages/CreatePermissionPage.vue'),
        meta: { title: 'Add Permission' },
      },
      {
        path: 'permissions/:id/edit',
        name: 'permissions-edit',
        component: () => import('../pages/EditPermissionPage.vue'),
        meta: { title: 'Edit Permission' },
      },
      {
        path: 'permissions/:id',
        name: 'permission-details',
        component: () => import('../pages/PermissionDetailsPage.vue'),
        meta: { title: 'Permission details' },
      },
      {
        path: 'activity-logs',
        name: 'activity-logs',
        component: () => import('../pages/ActivityLogsPage.vue'),
        meta: { title: 'System Logs' },
      },
      {
        path: 'activity-logs/:id',
        name: 'activity-log-details',
        component: () => import('../pages/ActivityLogDetailsPage.vue'),
        meta: { title: 'Activity log details' },
      },
      {
        path: 'system-monitoring',
        name: 'system-monitoring',
        component: () => import('../pages/SystemMonitoringOverviewPage.vue'),
        meta: { title: 'System Monitoring' },
      },
      {
        path: 'system-monitoring/login-history',
        name: 'login-history',
        component: () => import('../pages/LoginHistoryPage.vue'),
        meta: { title: 'Login History' },
      },
      {
        path: 'system-monitoring/active-sessions',
        name: 'active-sessions',
        component: () => import('../pages/ActiveSessionsPage.vue'),
        meta: { title: 'Active Sessions' },
      },
      {
        path: 'system-maintenance',
        name: 'system-maintenance',
        component: () => import('../pages/SystemMaintenancePage.vue'),
        meta: { title: 'Maintenance Mode' },
      },
      {
        path: 'system-backups',
        name: 'system-backups',
        component: () => import('../pages/SystemBackupsPage.vue'),
        meta: { title: 'Backups' },
      },
      {
        path: 'system-backups/:id',
        name: 'backup-details',
        component: () => import('../pages/SystemBackupDetailsPage.vue'),
        meta: { title: 'Backup details' },
      },
      {
        path: 'admin-guide/categories',
        name: 'admin-guide-categories',
        component: () => import('../pages/AdminGuideCategoriesPage.vue'),
        meta: { title: 'Guide Categories' },
      },
      {
        path: 'admin-guide/guides',
        name: 'admin-guide-guides',
        component: () => import('../pages/AdminGuideGuidesPage.vue'),
        meta: { title: 'Guides' },
      },
      {
        path: 'admin-guide/guides/create',
        name: 'admin-guide-guides-create',
        component: () => import('../pages/CreateAdminGuidePage.vue'),
        meta: { title: 'Add Guide' },
      },
      {
        path: 'admin-guide/guides/:id/edit',
        name: 'admin-guide-guides-edit',
        component: () => import('../pages/EditAdminGuidePage.vue'),
        meta: { title: 'Edit Guide' },
      },
      {
        path: 'help',
        name: 'help',
        component: () => import('../pages/HelpCenterPage.vue'),
        meta: { title: 'Help' },
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
