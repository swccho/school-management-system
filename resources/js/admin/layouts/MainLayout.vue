<template>
  <div class="flex min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <AdminSidebar />
    <div class="flex flex-1 flex-col min-w-0 md:pl-64">
      <AdminHeader />
      <main class="flex-1 p-4 md:p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { onMounted, provide, ref } from 'vue';
import AdminSidebar from '../components/AdminSidebar.vue';
import AdminHeader from '../components/AdminHeader.vue';
import { getSchoolSettings } from '../services/schoolService.js';

const dateTimeSettings = ref({
  date_format: 'Y-m-d',
  time_format: 'H:i',
  timezone: '',
});

provide('dateTimeSettings', dateTimeSettings);

onMounted(async () => {
  try {
    const data = await getSchoolSettings();
    dateTimeSettings.value = {
      date_format: data.date_format || 'Y-m-d',
      time_format: data.time_format || 'H:i',
      timezone: data.timezone || '',
    };
  } catch {
    // Keep defaults if settings fail to load
  }
});
</script>
