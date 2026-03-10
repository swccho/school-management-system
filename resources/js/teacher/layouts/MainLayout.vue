<template>
  <div class="flex min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <TeacherSidebar />
    <div class="flex min-w-0 flex-1 flex-col md:pl-64">
      <TeacherHeader />
      <main class="flex-1 p-4 md:p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import TeacherSidebar from '../components/TeacherSidebar.vue';
import TeacherHeader from '../components/TeacherHeader.vue';
</script>
