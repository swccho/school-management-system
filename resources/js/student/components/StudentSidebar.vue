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
            v-if="item.to"
            :to="item.to"
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
            :class="isActive(item.to) ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100'"
          >
            <component :is="item.icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
            <span>{{ item.label }}</span>
          </router-link>
          <span
            v-else
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-400 dark:text-zinc-500 cursor-not-allowed"
            :title="item.placeholder"
          >
            <component v-if="item.icon" :is="item.icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
            <span>{{ item.label }}</span>
          </span>
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
  CalendarDays,
  Megaphone,
  ClipboardList,
  Award,
  ClipboardCheck,
  BookOpen,
  CalendarCheck,
  Bell,
  MessageSquare,
  Wallet,
} from 'lucide-vue-next';
import { useAppStore } from '../stores/appStore.js';

const route = useRoute();
const appStore = useAppStore();
const sidebarOpen = computed(() => appStore.sidebarOpen);
const portalTitle = computed(() => appStore.portalTitle);
const toggleSidebar = () => appStore.toggleSidebar();

const navItems = [
  { name: 'dashboard', label: 'Dashboard', to: '/student/dashboard', icon: LayoutGrid },
  { name: 'profile', label: 'My Profile', to: '/student/profile', icon: User },
  { name: 'routine', label: 'Class Routine', to: '/student/routine', icon: CalendarDays },
  { name: 'announcements', label: 'Announcements', to: '/student/announcements', icon: Megaphone },
  { name: 'assignments', label: 'Assignments', to: '/student/assignments', icon: ClipboardList },
  { name: 'results', label: 'Results', to: '/student/results', icon: Award },
  { name: 'attendance', label: 'Attendance', to: '/student/attendance', icon: ClipboardCheck },
  { name: 'study-materials', label: 'Study Materials', to: '/student/materials', icon: BookOpen },
  { name: 'exam-schedule', label: 'Exam Schedule', to: '/student/exams', icon: CalendarCheck },
  { name: 'notifications', label: 'Notifications', to: '/student/notifications', icon: Bell },
  { name: 'messages', label: 'Messages', to: '/student/messages', icon: MessageSquare },
  { name: 'fees', label: 'Fees', to: '/student/fees', icon: Wallet },
];

function isActive(to) {
  if (to === '/student/dashboard') return route.path === '/student' || route.path === '/student/dashboard';
  return route.path.startsWith(to);
}
</script>
