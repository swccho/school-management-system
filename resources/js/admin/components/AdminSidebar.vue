<template>
  <aside
    class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col border-r border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 transition-transform duration-200 md:translate-x-0"
    :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
  >
    <div class="flex h-14 shrink-0 items-center border-b border-zinc-200 px-4 dark:border-zinc-800">
      <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
        {{ portalTitle }}
      </span>
    </div>
    <nav class="sidenav-scroll flex-1 overflow-y-auto p-4">
      <ul class="space-y-1">
        <template v-for="item in navItems" :key="item.name">
          <li v-if="item.isGroup" class="mt-4 pt-2 first:mt-0 first:pt-0">
            <span class="px-3 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
              {{ item.label }}
            </span>
          </li>
          <li v-else>
            <router-link
              :to="item.to"
              class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
              :class="isActive(item.to)
                ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100'
                : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100'"
            >
              <span>{{ item.label }}</span>
            </router-link>
          </li>
        </template>
      </ul>
    </nav>
  </aside>
  <!-- Sidebar backdrop on mobile -->
  <div
    v-if="sidebarOpen"
    class="fixed inset-0 z-30 bg-zinc-900/50 md:hidden"
    aria-hidden="true"
    @click="toggleSidebar"
  />
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAppStore } from '../stores/appStore.js';

const route = useRoute();
const appStore = useAppStore();

const sidebarOpen = computed(() => appStore.sidebarOpen);
const portalTitle = computed(() => appStore.portalTitle);
const toggleSidebar = () => appStore.toggleSidebar();

const navItems = [
  { name: 'dashboard', label: 'Dashboard', to: '/admin/dashboard' },
  { name: 'users', label: 'Users', to: '/admin/users' },
  { name: 'academic-group', label: 'Academic', to: null, isGroup: true },
  { name: 'academic-sessions', label: 'Sessions', to: '/admin/academic/sessions' },
  { name: 'academic-classes', label: 'Classes', to: '/admin/academic/classes' },
  { name: 'academic-sections', label: 'Sections', to: '/admin/academic/sections' },
  { name: 'academic-subjects', label: 'Subjects', to: '/admin/academic/subjects' },
  { name: 'teacher-subject-assignments', label: 'Teacher Subject Assignments', to: '/admin/academic/teacher-subject-assignments' },
  { name: 'staff-group', label: 'Staff Management', to: null, isGroup: true },
  { name: 'staff-staffs', label: 'Staff', to: '/admin/staff/staffs' },
  { name: 'staff-teachers', label: 'Teachers', to: '/admin/staff/teachers' },
  { name: 'staff-departments', label: 'Departments', to: '/admin/staff/departments' },
  { name: 'staff-designations', label: 'Designations', to: '/admin/staff/designations' },
  { name: 'students', label: 'Students', to: '/admin/students' },
  { name: 'attendance', label: 'Attendance', to: '/admin/attendance' },
  { name: 'routines', label: 'Class Routines', to: '/admin/routines' },
  { name: 'exams-group', label: 'Examination', to: null, isGroup: true },
  { name: 'exam-types', label: 'Exam Types', to: '/admin/exams/types' },
  { name: 'exams', label: 'Exams', to: '/admin/exams' },
  { name: 'marks-entry', label: 'Marks Entry', to: '/admin/marks-entry' },
  { name: 'grade-scales', label: 'Grade Scales', to: '/admin/results/grade-scales' },
  { name: 'results', label: 'Results', to: '/admin/results' },
  { name: 'access-control-group', label: 'Access Control', to: null, isGroup: true },
  { name: 'roles', label: 'Roles', to: '/admin/roles' },
  { name: 'permissions', label: 'Permissions', to: '/admin/permissions' },
  { name: 'settings-group', label: 'Settings', to: null, isGroup: true },
  { name: 'school-profile', label: 'School Profile', to: '/admin/settings/school-profile' },
  { name: 'system-settings', label: 'System Settings', to: '/admin/settings/system' },
];

function isActive(to) {
  if (to === '/admin/dashboard') {
    return route.path === '/admin/dashboard' || route.path === '/admin';
  }
  return route.path.startsWith(to);
}
</script>
