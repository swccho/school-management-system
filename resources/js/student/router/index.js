import { createRouter, createWebHistory } from 'vue-router';
import StudentLayout from '../layouts/StudentLayout.vue';
import { useAuthStore } from '../stores/authStore.js';

const routes = [
  {
    path: '/student/login',
    name: 'student-login',
    component: () => import('../pages/LoginPage.vue'),
    meta: { title: 'Student Login', public: true },
  },
  {
    path: '/student',
    component: StudentLayout,
    children: [
      { path: '', redirect: '/student/dashboard' },
      { path: 'dashboard', name: 'student-dashboard', component: () => import('../pages/DashboardPage.vue'), meta: { title: 'Dashboard' } },
      { path: 'profile', name: 'student-profile', component: () => import('../pages/ProfilePage.vue'), meta: { title: 'My Profile' } },
      { path: 'routine', name: 'student-routine', component: () => import('../pages/RoutinePage.vue'), meta: { title: 'Class Routine' } },
      { path: 'announcements', name: 'student-announcements', component: () => import('../pages/AnnouncementsPage.vue'), meta: { title: 'Announcements' } },
      { path: 'announcements/:id', name: 'student-notice-detail', component: () => import('../pages/NoticeDetailPage.vue'), meta: { title: 'Notice' } },
      { path: 'assignments', name: 'student-assignments', component: () => import('../pages/AssignmentsPage.vue'), meta: { title: 'Assignments' } },
      { path: 'assignments/:id', name: 'student-assignment-view', component: () => import('../pages/AssignmentViewPage.vue'), meta: { title: 'Assignment' } },
      { path: 'results', name: 'student-results', component: () => import('../pages/ResultsPage.vue'), meta: { title: 'Results' } },
      { path: 'attendance', name: 'student-attendance', component: () => import('../pages/AttendancePage.vue'), meta: { title: 'Attendance' } },
      { path: 'materials', name: 'student-materials', component: () => import('../pages/MaterialsPage.vue'), meta: { title: 'Study Materials' } },
      { path: 'exams', name: 'student-exams', component: () => import('../pages/ExamsPage.vue'), meta: { title: 'Exam Schedule' } },
      { path: 'notifications', name: 'student-notifications', component: () => import('../pages/NotificationsPage.vue'), meta: { title: 'Notifications' } },
      { path: 'messages', name: 'student-messages', component: () => import('../pages/MessagesPage.vue'), meta: { title: 'Messages' } },
      { path: 'fees', name: 'student-fees', component: () => import('../pages/FeesPage.vue'), meta: { title: 'Fees' } },
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
  const isAuthenticated = authStore.isAuthenticated;

  if (isPublic && isAuthenticated) {
    return { path: '/student/dashboard' };
  }
  if (!isPublic && !isAuthenticated) {
    return { path: '/student/login', query: { redirect: to.fullPath } };
  }
  return true;
});

export default router;
