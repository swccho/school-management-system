<template>
  <div class="flex min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <StudentSidebar />
    <div class="flex min-w-0 flex-1 flex-col md:pl-64">
      <StudentHeader />
      <main class="flex-1 p-4 md:p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import StudentSidebar from '../components/StudentSidebar.vue';
import StudentHeader from '../components/StudentHeader.vue';
</script>
