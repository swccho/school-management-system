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
              <component :is="item.icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
              <span>{{ item.label }}</span>
            </router-link>
          </li>
        </template>
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
  Users,
  Calendar,
  School,
  Layers,
  BookOpen,
  Link2,
  UserSquare,
  Building,
  Badge,
  GraduationCap,
  ClipboardCheck,
  CalendarDays,
  FileText,
  ClipboardList,
  PenLine,
  Gauge,
  Award,
  BellRing,
  Newspaper,
  Image,
  Download,
  ImagePlus,
  Shield,
  Key,
  ScrollText,
  Activity,
  History,
  Monitor,
  Settings,
  Database,
  Folder,
  HelpCircle,
} from 'lucide-vue-next';
import { useAppStore } from '../stores/appStore.js';

const route = useRoute();
const appStore = useAppStore();

const sidebarOpen = computed(() => appStore.sidebarOpen);
const portalTitle = computed(() => appStore.portalTitle);
const toggleSidebar = () => appStore.toggleSidebar();

const navItems = [
  { name: 'dashboard', label: 'Dashboard', to: '/admin/dashboard', icon: LayoutGrid },
  { name: 'users', label: 'Users', to: '/admin/users', icon: Users },
  { name: 'academic-group', label: 'Academic', to: null, isGroup: true },
  { name: 'academic-sessions', label: 'Sessions', to: '/admin/academic/sessions', icon: Calendar },
  { name: 'academic-classes', label: 'Classes', to: '/admin/academic/classes', icon: School },
  { name: 'academic-sections', label: 'Sections', to: '/admin/academic/sections', icon: Layers },
  { name: 'academic-subjects', label: 'Subjects', to: '/admin/academic/subjects', icon: BookOpen },
  { name: 'teacher-subject-assignments', label: 'Teacher Subject Assignments', to: '/admin/academic/teacher-subject-assignments', icon: Link2 },
  { name: 'staff-group', label: 'Staff Management', to: null, isGroup: true },
  { name: 'staff-staffs', label: 'Staff', to: '/admin/staff/staffs', icon: Users },
  { name: 'staff-teachers', label: 'Teachers', to: '/admin/staff/teachers', icon: UserSquare },
  { name: 'staff-departments', label: 'Departments', to: '/admin/staff/departments', icon: Building },
  { name: 'staff-designations', label: 'Designations', to: '/admin/staff/designations', icon: Badge },
  { name: 'students', label: 'Students', to: '/admin/students', icon: GraduationCap },
  { name: 'attendance', label: 'Attendance', to: '/admin/attendance', icon: ClipboardCheck },
  { name: 'routines', label: 'Class Routines', to: '/admin/routines', icon: CalendarDays },
  { name: 'exams-group', label: 'Examination', to: null, isGroup: true },
  { name: 'exam-types', label: 'Exam Types', to: '/admin/exams/types', icon: FileText },
  { name: 'exams', label: 'Exams', to: '/admin/exams', icon: ClipboardList },
  { name: 'marks-entry', label: 'Marks Entry', to: '/admin/marks-entry', icon: PenLine },
  { name: 'grade-scales', label: 'Grade Scales', to: '/admin/results/grade-scales', icon: Gauge },
  { name: 'results', label: 'Results', to: '/admin/results', icon: Award },
  { name: 'content-group', label: 'Content', to: null, isGroup: true },
  { name: 'notices', label: 'Notices', to: '/admin/notices', icon: BellRing },
  { name: 'news-posts', label: 'News', to: '/admin/news-posts', icon: Newspaper },
  { name: 'events', label: 'Events', to: '/admin/events', icon: Calendar },
  { name: 'galleries', label: 'Galleries', to: '/admin/galleries', icon: Image },
  { name: 'downloads', label: 'Downloads', to: '/admin/downloads', icon: Download },
  { name: 'pages', label: 'Pages', to: '/admin/pages', icon: FileText },
  { name: 'banners', label: 'Banners', to: '/admin/banners', icon: ImagePlus },
  { name: 'homepage-sections', label: 'Homepage Sections', to: '/admin/homepage-sections', icon: LayoutGrid },
  { name: 'access-control-group', label: 'Access Control', to: null, isGroup: true },
  { name: 'roles', label: 'Roles', to: '/admin/roles', icon: Shield },
  { name: 'permissions', label: 'Permissions', to: '/admin/permissions', icon: Key },
  { name: 'activity-logs', label: 'System Logs', to: '/admin/activity-logs', icon: ScrollText },
  { name: 'system-monitoring-group', label: 'System Monitoring', to: null, isGroup: true },
  { name: 'system-monitoring', label: 'Overview', to: '/admin/system-monitoring', icon: Activity },
  { name: 'login-history', label: 'Login History', to: '/admin/system-monitoring/login-history', icon: History },
  { name: 'active-sessions', label: 'Active Sessions', to: '/admin/system-monitoring/active-sessions', icon: Monitor },
  { name: 'maintenance-group', label: 'Backup & Maintenance', to: null, isGroup: true },
  { name: 'system-maintenance', label: 'Maintenance Mode', to: '/admin/system-maintenance', icon: Settings },
  { name: 'system-backups', label: 'Backups', to: '/admin/system-backups', icon: Database },
  { name: 'admin-guide-group', label: 'Admin Guide', to: null, isGroup: true },
  { name: 'admin-guide-categories', label: 'Categories', to: '/admin/admin-guide/categories', icon: Folder },
  { name: 'admin-guide-guides', label: 'Guides', to: '/admin/admin-guide/guides', icon: BookOpen },
  { name: 'help', label: 'Help', to: '/admin/help', icon: HelpCircle },
  { name: 'settings-group', label: 'Settings', to: null, isGroup: true },
  { name: 'school-profile', label: 'School Profile', to: '/admin/settings/school-profile', icon: Building },
  { name: 'system-settings', label: 'System Settings', to: '/admin/settings/system', icon: Settings },
];

function isActive(to) {
  if (to === '/admin/dashboard') {
    return route.path === '/admin/dashboard' || route.path === '/admin';
  }
  return route.path.startsWith(to);
}
</script>
