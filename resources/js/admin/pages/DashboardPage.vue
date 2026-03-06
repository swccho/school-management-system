<template>
  <PageContainer title="Dashboard" description="Overview of your school management system.">
    <div class="space-y-8">
      <section>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">
          Welcome to the admin portal. Use the sidebar to manage users, academic setup, and settings.
        </p>
      </section>

      <section>
        <h3 class="mb-4 text-sm font-medium text-zinc-700 dark:text-zinc-300">Overview</h3>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <StatCard title="Students" value="—" supportingText="Placeholder" />
          <StatCard title="Teachers" value="—" supportingText="Placeholder" />
          <StatCard title="Classes" value="—" supportingText="Placeholder" />
          <StatCard title="Notices" value="—" supportingText="Placeholder" />
        </div>
      </section>

      <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent activity</h3>
          <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
            Activity feed will appear here once modules are connected.
          </p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Quick actions</h3>
          <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
            Shortcuts to common tasks will be added with each module.
          </p>
        </div>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import PageContainer from '../components/PageContainer.vue';
import StatCard from '../components/StatCard.vue';
</script>
