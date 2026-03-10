<template>
  <header class="sticky top-0 z-20 flex h-14 shrink-0 items-center justify-between border-b border-zinc-200 bg-white px-4 dark:border-zinc-800 dark:bg-zinc-900 md:px-6">
    <div class="flex items-center gap-4">
      <button
        type="button"
        class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-300 md:hidden"
        aria-label="Toggle sidebar"
        @click="toggleSidebar"
      >
        <Menu class="h-5 w-5" />
      </button>
      <div>
        <h1 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ pageTitle }}</h1>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <div class="relative">
        <button
          type="button"
          class="relative rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
          aria-label="Notifications"
          @click="toggleNotificationDropdown"
        >
          <Bell class="h-5 w-5" />
          <span v-if="unreadCount > 0" class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-blue-600 px-1 text-[10px] font-medium text-white">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
        </button>
        <div v-if="notificationDropdownOpen" class="absolute right-0 top-full z-50 mt-1 w-80 rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-800 dark:bg-zinc-900" @click.stop>
          <div class="max-h-96 overflow-y-auto p-2">
            <div v-if="notificationDropdownLoading" class="py-4 text-center text-sm text-zinc-500">Loading…</div>
            <template v-else>
              <div v-if="!recentNotifications.length" class="py-4 text-center text-sm text-zinc-500">No notifications</div>
              <template v-else>
                <div
                  v-for="n in recentNotifications"
                  :key="n.id"
                  class="rounded-lg p-3 text-left transition"
                  :class="n.read_at ? '' : 'bg-zinc-50 dark:bg-zinc-800/50'"
                >
                  <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ n.title }}</p>
                  <p class="mt-0.5 text-xs text-zinc-500">{{ formatNotificationDate(n.created_at) }}</p>
                  <router-link v-if="n.action_url" :to="n.action_url" class="mt-1 block text-xs text-blue-600 dark:text-blue-400" @click="notificationDropdownOpen = false">View</router-link>
                </div>
              </template>
            </template>
          </div>
          <div class="border-t border-zinc-200 p-2 dark:border-zinc-800">
            <router-link to="/teacher/notifications" class="block rounded-lg py-2 text-center text-sm font-medium text-zinc-600 dark:text-zinc-400" @click="notificationDropdownOpen = false">See all</router-link>
          </div>
        </div>
      </div>
      <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ teacherName }}</span>
      <button
        type="button"
        class="rounded-lg px-3 py-1.5 text-sm font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
        @click="handleLogout"
      >
        Log out
      </button>
    </div>
    <div v-if="notificationDropdownOpen" class="fixed inset-0 z-40" aria-hidden="true" @click="notificationDropdownOpen = false" />
  </header>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Menu, Bell } from 'lucide-vue-next';
import { useAppStore } from '../stores/appStore.js';
import { useAuthStore } from '../stores/authStore.js';
import { useTeacherAuth } from '../composables/useTeacherAuth.js';
import { getNotifications, getUnreadCount } from '../services/notificationService.js';

const route = useRoute();
const router = useRouter();
const appStore = useAppStore();
const authStore = useAuthStore();
const { logout } = useTeacherAuth();

const notificationDropdownOpen = ref(false);
const unreadCount = ref(0);
const recentNotifications = ref([]);
const notificationDropdownLoading = ref(false);

const toggleSidebar = () => appStore.toggleSidebar();
const pageTitle = computed(() => route.meta?.title ?? 'Teachers Portal');
const teacherName = computed(() => authStore.teacherName);

function formatNotificationDate(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  const now = new Date();
  const diff = now - d;
  if (diff < 60000) return 'Just now';
  if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`;
  if (diff < 86400000) return `${Math.floor(diff / 3600000)}h ago`;
  return d.toLocaleDateString();
}

async function fetchUnreadCount() {
  try {
    const res = await getUnreadCount();
    unreadCount.value = res?.count ?? 0;
  } catch {
    unreadCount.value = 0;
  }
}

async function toggleNotificationDropdown() {
  notificationDropdownOpen.value = !notificationDropdownOpen.value;
  if (notificationDropdownOpen.value) {
    notificationDropdownLoading.value = true;
    try {
      const res = await getNotifications({ per_page: 10 });
      recentNotifications.value = res?.data ?? [];
    } catch {
      recentNotifications.value = [];
    } finally {
      notificationDropdownLoading.value = false;
    }
    fetchUnreadCount();
  }
}

async function handleLogout() {
  await logout();
  router.push('/teacher/login');
}

onMounted(fetchUnreadCount);
</script>
