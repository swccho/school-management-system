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
import AdminSidebar from '../components/AdminSidebar.vue';
import AdminHeader from '../components/AdminHeader.vue';
</script>
