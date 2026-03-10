import { createRouter, createWebHistory } from 'vue-router';
import MainLayout from '../layouts/MainLayout.vue';
import { useAuthStore } from '../stores/authStore.js';

const routes = [
  {
    path: '/teacher/login',
    name: 'teacher-login',
    component: () => import('../pages/LoginPage.vue'),
    meta: { title: 'Teacher Login', public: true },
  },
  {
    path: '/teacher',
    component: MainLayout,
    children: [
      { path: '', redirect: '/teacher/dashboard' },
      { path: 'dashboard', name: 'teacher-dashboard', component: () => import('../pages/DashboardPage.vue'), meta: { title: 'Dashboard' } },
      { path: 'profile', name: 'teacher-profile', component: () => import('../pages/ProfilePage.vue'), meta: { title: 'My Profile' } },
      { path: 'my-classes', name: 'teacher-my-classes', component: () => import('../pages/MyClassesPage.vue'), meta: { title: 'My Classes' } },
      { path: 'routine', name: 'teacher-routine', component: () => import('../pages/RoutinePage.vue'), meta: { title: 'Class Routine' } },
      { path: 'attendance', name: 'teacher-attendance', component: () => import('../pages/AttendanceListPage.vue'), meta: { title: 'Attendance' } },
      { path: 'attendance/take/:sessionId', name: 'teacher-attendance-take', component: () => import('../pages/TakeAttendancePage.vue'), meta: { title: 'Take Attendance' } },
      { path: 'students', name: 'teacher-students', component: () => import('../pages/StudentsPage.vue'), meta: { title: 'Students' } },
      { path: 'homework', name: 'teacher-homework', component: () => import('../pages/HomeworkListPage.vue'), meta: { title: 'Homework' } },
      { path: 'homework/create', name: 'teacher-homework-create', component: () => import('../pages/HomeworkFormPage.vue'), meta: { title: 'Add Homework' } },
      { path: 'homework/:id/edit', name: 'teacher-homework-edit', component: () => import('../pages/HomeworkFormPage.vue'), meta: { title: 'Edit Homework' } },
      { path: 'lesson-plans', name: 'teacher-lesson-plans', component: () => import('../pages/LessonPlansPage.vue'), meta: { title: 'Lesson Plans' } },
      { path: 'exams', name: 'teacher-exams', component: () => import('../pages/ExamsPage.vue'), meta: { title: 'Exams & Marks' } },
      { path: 'exams/:examId/marks', name: 'teacher-marks-entry', component: () => import('../pages/EnterMarksPage.vue'), meta: { title: 'Enter Marks' } },
      { path: 'notices', name: 'teacher-notices', component: () => import('../pages/NoticesPage.vue'), meta: { title: 'Notices' } },
      { path: 'calendar', name: 'teacher-calendar', component: () => import('../pages/CalendarPage.vue'), meta: { title: 'Calendar' } },
      { path: 'leave', name: 'teacher-leave', component: () => import('../pages/LeaveRequestPage.vue'), meta: { title: 'Leave Request' } },
      { path: 'learning-materials', name: 'teacher-learning-materials', component: () => import('../pages/LearningMaterialsPage.vue'), meta: { title: 'Learning Materials' } },
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
    return { path: '/teacher/dashboard' };
  }
  if (!isPublic && !isAuthenticated) {
    return { path: '/teacher/login', query: { redirect: to.fullPath } };
  }
  return true;
});

export default router;
