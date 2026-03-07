<template>
  <PageContainer
    title="Marks Entry"
    description="View marks entry history and enter marks for exams by class, section and subject."
  >
    <template #actions>
      <router-link
        to="/admin/marks-entry/create"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
      >
        Enter Marks
      </router-link>
    </template>

    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <MarksEntryFilterForm
        v-model="filters"
        :show-subject="true"
      />
      <div class="flex gap-2">
        <button
          type="button"
          class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
          @click="fetch"
        >
          Apply Filters
        </button>
        <button
          type="button"
          class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
          @click="resetFilters"
        >
          Reset
        </button>
      </div>
    </div>

      <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          Loading…
        </div>
        <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
          {{ error }}
        </div>
        <div v-else-if="list.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          No marks entry records yet. Use “Enter Marks” to add marks for an exam.
        </div>
        <div v-else class="sidenav-scroll overflow-x-auto">
          <table class="w-full min-w-[600px]">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800">
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Exam</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Class</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Section</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">Students</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Updated</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(item, idx) in list"
                :key="`${item.exam_id}-${item.class_id}-${item.section_id ?? 'n'}-${item.subject_id}-${idx}`"
                class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
              >
                <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ item.exam_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ item.class_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ item.section_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ item.subject_name ?? '—' }}</td>
                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ item.students_count ?? 0 }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="item.status === 'submitted' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                  >
                    {{ item.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">{{ item.updated_at_formatted ?? formatDate(item.updated_at) ?? '—' }}</td>
                <td class="px-4 py-3">
                  <router-link
                    :to="detailRoute(item)"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  >
                    View
                  </router-link>
                  <span class="mx-1 text-zinc-300 dark:text-zinc-600">|</span>
                  <router-link
                    :to="editRoute(item)"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  >
                    Edit
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
  </PageContainer>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import MarksEntryFilterForm from '../components/MarksEntryFilterForm.vue';
import { getMarksEntryList } from '../services/marksEntryService.js';

const loading = ref(true);
const error = ref(null);
const list = ref([]);
function initialFilters() {
  return { exam_id: null, class_id: null, section_id: null, subject_id: null };
}

const filters = ref(initialFilters());

function resetFilters() {
  filters.value = initialFilters();
  fetch();
}

function formatDate(iso) {
  if (!iso) return '—';
  try {
    return new Date(iso).toLocaleDateString();
  } catch {
    return iso;
  }
}

function detailRoute(item) {
  const q = new URLSearchParams({
    exam_id: item.exam_id,
    class_id: item.class_id,
    subject_id: item.subject_id,
  });
  if (item.section_id) q.set('section_id', item.section_id);
  return `/admin/marks-entry/detail?${q.toString()}`;
}

function editRoute(item) {
  const q = new URLSearchParams({
    exam_id: item.exam_id,
    class_id: item.class_id,
    subject_id: item.subject_id,
  });
  if (item.section_id) q.set('section_id', item.section_id);
  return `/admin/marks-entry/create?${q.toString()}`;
}

async function fetch() {
  loading.value = true;
  error.value = null;
  try {
    const params = {};
    if (filters.value.exam_id) params.exam_id = filters.value.exam_id;
    if (filters.value.class_id) params.class_id = filters.value.class_id;
    if (filters.value.section_id) params.section_id = filters.value.section_id;
    if (filters.value.subject_id) params.subject_id = filters.value.subject_id;
    list.value = await getMarksEntryList(params);
  } catch {
    error.value = 'Failed to load marks entry list.';
    list.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(() => fetch());
</script>
