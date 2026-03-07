<template>
  <PageContainer
    title="Students"
    description="Manage student records. Add students and link guardians for contact and communication."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreate"
      >
        Add Student
      </button>
    </template>

    <div class="space-y-4">
      <div class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
          <label for="student-search" class="sr-only">Search</label>
          <input
            id="student-search"
            v-model="searchQuery"
            type="search"
            placeholder="Search by admission no, name…"
            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            @input="onSearchInput"
          />
        </div>
        <div>
          <label for="student-status" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
          <select
            id="student-status"
            v-model="filterStatus"
            class="mt-1 block rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            @change="fetch"
          >
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="archived">Archived</option>
          </select>
        </div>
      </div>

      <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          Loading students…
        </div>
        <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
          {{ error }}
        </div>
        <div v-else-if="students.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          No students yet. Add one to get started.
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[700px]">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800">
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Admission No</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Gender</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Phone</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Guardian</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="s in students"
                :key="s.id"
                class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
              >
                <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ s.admission_no }}</td>
                <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ s.full_name }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.gender ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.phone ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.primary_guardian_name ?? '—' }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="s.status === 'active'
                      ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                      : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                  >
                    {{ s.status }}
                  </span>
                </td>
                <td class="px-4 py-3 flex gap-2">
                  <router-link
                    :to="{ name: 'student-details', params: { id: s.id } }"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  >
                    View
                  </router-link>
                  <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="openEdit(s)"
                  >
                    Edit
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <StudentForm
      v-model="modalOpen"
      :student="editing"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import StudentForm from '../components/StudentForm.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getStudents } from '../services/studentService.js';

const toast = useToast();

const loading = ref(true);
const error = ref(null);
const students = ref([]);
const modalOpen = ref(false);
const editing = ref(null);
const searchQuery = ref('');
const filterStatus = ref('');
let searchTimeout = null;

async function fetch() {
  loading.value = true;
  error.value = null;
  try {
    const params = {};
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim();
    if (filterStatus.value) params.status = filterStatus.value;
    students.value = await getStudents(params);
  } catch {
    error.value = 'Failed to load students.';
    students.value = [];
  } finally {
    loading.value = false;
  }
}

function onSearchInput() {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(fetch, 300);
}

function openCreate() {
  editing.value = null;
  modalOpen.value = true;
}

function openEdit(s) {
  editing.value = s;
  modalOpen.value = true;
}

function onSaved() {
  fetch();
  toast.success(editing.value ? 'Student updated successfully.' : 'Student created successfully.');
}

onMounted(fetch);
</script>
