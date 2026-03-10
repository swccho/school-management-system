<template>
  <PageContainer
    title="Backup details"
    description="View backup metadata."
  >
    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading…
    </div>
    <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else class="space-y-6">
      <router-link
        :to="{ name: 'system-backups' }"
        class="inline-block text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
      >
        Back to backups
      </router-link>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Backup</h3>
        <dl class="mt-4 space-y-2">
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">File name</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ backup.file_name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Type</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ backup.backup_type ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Size</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ backup.file_size_formatted ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Status</dt>
            <dd>
              <span
                class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                :class="backup.status === 'completed'
                  ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                  : backup.status === 'failed'
                    ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'
                    : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'"
              >
                {{ backup.status }}
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Created by</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ backup.created_by_name ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Created at</dt>
            <dd class="text-sm text-zinc-600 dark:text-zinc-400">{{ backup.created_at_formatted ?? '—' }}</dd>
          </div>
          <div v-if="backup.file_url">
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Download</dt>
            <dd>
              <a
                :href="backup.file_url"
                target="_blank"
                rel="noopener noreferrer"
                class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
              >
                Download file
              </a>
            </dd>
          </div>
        </dl>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getBackup } from '../services/backupService.js';

const route = useRoute();
const backup = ref({});
const loading = ref(true);
const error = ref(null);

const backupId = computed(() => route.params.id);

async function loadBackup() {
  if (!backupId.value) return;
  loading.value = true;
  error.value = null;
  try {
    backup.value = await getBackup(backupId.value);
  } catch {
    error.value = 'Failed to load backup.';
    backup.value = {};
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadBackup();
});
</script>
