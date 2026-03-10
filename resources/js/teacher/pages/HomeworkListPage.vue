<template>
  <PageContainer title="Homework" description="Create and manage homework for your classes.">
    <div class="mb-4 flex flex-wrap items-end gap-4">
      <router-link
        to="/teacher/homework/create"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900"
      >
        Add Homework
      </router-link>
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Class · Section · Subject</label>
        <select v-model="filterClassSectionSubject" class="mt-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
          <option value="">All</option>
          <option v-for="a in assignments" :key="a.class_id + '-' + a.section_id + '-' + a.subject_id" :value="a.class_id + '-' + a.section_id + '-' + a.subject_id">{{ a.class_name }} · {{ a.section_name }} · {{ a.subject_name }}</option>
        </select>
      </div>
      <button type="button" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600" @click="load">Apply</button>
    </div>
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="items.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      No homework yet. Create one to get started.
    </div>
    <div v-else class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <table class="w-full min-w-[500px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Class · Section · Subject</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Due date</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="h in items" :key="h.id" class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0">
            <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ h.title }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ h.class_name }} · {{ h.section_name }} · {{ h.subject_name }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ h.due_date ?? '—' }}</td>
            <td class="px-4 py-3">
              <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300">{{ h.status ?? 'active' }}</span>
            </td>
            <td class="px-4 py-3 text-right">
              <router-link :to="`/teacher/homework/${h.id}/edit`" class="text-sm text-blue-600 hover:underline dark:text-blue-400">Edit</router-link>
              <button type="button" class="ml-3 text-sm text-red-600 hover:underline dark:text-red-400" @click="confirmDelete(h)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { useAuthStore } from '../stores/authStore.js';
import { getHomeworkList, deleteHomework } from '../services/homeworkService.js';

const router = useRouter();
const authStore = useAuthStore();
const assignments = computed(() => authStore.assignments ?? []);
const loading = ref(true);
const items = ref([]);
const filterClassSectionSubject = ref('');

async function load() {
  loading.value = true;
  try {
    const params = {};
    if (filterClassSectionSubject.value) {
      const [class_id, section_id, subject_id] = filterClassSectionSubject.value.split('-').map(Number);
      params.class_id = class_id;
      params.section_id = section_id;
      params.subject_id = subject_id;
    }
    items.value = await getHomeworkList(params);
  } catch {
    items.value = [];
  } finally {
    loading.value = false;
  }
}

function confirmDelete(h) {
  if (!window.confirm(`Delete "${h.title}"?`)) return;
  deleteHomework(h.id).then(() => load()).catch(() => {});
}

onMounted(load);
</script>
