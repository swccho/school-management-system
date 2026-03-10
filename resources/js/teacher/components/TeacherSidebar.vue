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
            <component :is="item.icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
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
import {
  LayoutGrid,
  User,
  School,
  CalendarDays,
  ClipboardCheck,
  GraduationCap,
  ClipboardList,
  BookOpen,
  BellRing,
  Calendar,
  MessageSquare,
  CalendarOff,
  BookMarked,
  FileBarChart,
  LineChart,
  Bell,
} from 'lucide-vue-next';
import { useAppStore } from '../stores/appStore.js';

const route = useRoute();
const appStore = useAppStore();
const sidebarOpen = computed(() => appStore.sidebarOpen);
const portalTitle = computed(() => appStore.portalTitle);
const toggleSidebar = () => appStore.toggleSidebar();

const navItems = [
  { name: 'dashboard', label: 'Dashboard', to: '/teacher/dashboard', icon: LayoutGrid },
  { name: 'profile', label: 'My Profile', to: '/teacher/profile', icon: User },
  { name: 'my-classes', label: 'My Classes', to: '/teacher/my-classes', icon: School },
  { name: 'routine', label: 'Class Routine', to: '/teacher/routine', icon: CalendarDays },
  { name: 'attendance', label: 'Attendance', to: '/teacher/attendance', icon: ClipboardCheck },
  { name: 'students', label: 'Students', to: '/teacher/students', icon: GraduationCap },
  { name: 'homework', label: 'Homework / Assignments', to: '/teacher/homework', icon: ClipboardList },
  { name: 'lesson-plans', label: 'Lesson Plans', to: '/teacher/lesson-plans', icon: BookOpen },
  { name: 'exams', label: 'Exams & Marks', to: '/teacher/exams', icon: ClipboardList },
  { name: 'notices', label: 'Notices', to: '/teacher/notices', icon: BellRing },
  { name: 'calendar', label: 'Calendar', to: '/teacher/calendar', icon: Calendar },
  { name: 'messages', label: 'Messages', to: '/teacher/messages', icon: MessageSquare },
  { name: 'leave', label: 'Leave Request', to: '/teacher/leave', icon: CalendarOff },
  { name: 'learning-materials', label: 'Learning Materials', to: '/teacher/learning-materials', icon: BookMarked },
  { name: 'reports', label: 'Reports', to: '/teacher/reports', icon: FileBarChart },
  { name: 'analytics', label: 'Analytics', to: '/teacher/analytics', icon: LineChart },
  { name: 'notifications', label: 'Notifications', to: '/teacher/notifications', icon: Bell },
];

function isActive(to) {
  if (to === '/teacher/dashboard') return route.path === '/teacher' || route.path === '/teacher/dashboard';
  return route.path.startsWith(to);
}
</script>
