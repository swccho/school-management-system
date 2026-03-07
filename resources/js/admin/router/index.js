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
