<template>
  <aside
    class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 transition-transform duration-200 md:translate-x-0"
    :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
  >
    <div class="flex h-14 shrink-0 items-center border-b border-zinc-200 px-4 dark:border-zinc-800">
      <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ portalTitle }}</span>
    </div>
    <nav class="sidenav-scroll flex-1 overflow-y-auto p-4">
      <ul class="space-y-1">
        <li v-for="item in navItems" :key="item.name">
          <router-link
            :to="item.to"
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
            :class="isActive(item.to) ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100'"
          >
            <span>{{ item.label }}</span>
          </router-link>
        </li>
      </ul>
    </nav>
  </aside>
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
  { name: 'dashboard', label: 'Dashboard', to: '/teacher/dashboard' },
  { name: 'profile', label: 'My Profile', to: '/teacher/profile' },
  { name: 'my-classes', label: 'My Classes', to: '/teacher/my-classes' },
  { name: 'routine', label: 'Class Routine', to: '/teacher/routine' },
  { name: 'attendance', label: 'Attendance', to: '/teacher/attendance' },
  { name: 'students', label: 'Students', to: '/teacher/students' },
  { name: 'homework', label: 'Homework / Assignments', to: '/teacher/homework' },
  { name: 'lesson-plans', label: 'Lesson Plans', to: '/teacher/lesson-plans' },
  { name: 'exams', label: 'Exams & Marks', to: '/teacher/exams' },
  { name: 'notices', label: 'Notices', to: '/teacher/notices' },
  { name: 'calendar', label: 'Calendar', to: '/teacher/calendar' },
  { name: 'leave', label: 'Leave Request', to: '/teacher/leave' },
  { name: 'learning-materials', label: 'Learning Materials', to: '/teacher/learning-materials' },
];

function isActive(to) {
  if (to === '/teacher/dashboard') return route.path === '/teacher' || route.path === '/teacher/dashboard';
  return route.path.startsWith(to);
}
</script>
