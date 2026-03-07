<template>
  <PageContainer
    title="Staff"
    description="Manage all school employees (teachers and non-teaching staff). Add staff first, then optionally add them as teachers."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreate"
      >
        Add Staff
      </button>
    </template>

    <div class="space-y-4">
      <div class="flex flex-wrap items-center gap-2">
        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</label>
        <select
          v-model="filterType"
          class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @change="fetch"
        >
          <option value="">All</option>
          <option value="teacher">Teacher</option>
          <option value="staff">Staff</option>
        </select>
      </div>

      <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          Loading staff…
        </div>
        <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
          {{ error }}
        </div>
        <div v-else-if="staffs.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          No staff yet. Add one to get started.
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[700px]">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800">
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Employee ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Department</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Designation</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Phone</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="s in staffs"
                :key="s.id"
                class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
              >
                <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ s.employee_id }}</td>
                <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ s.full_name }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="s.employee_type === 'teacher'
                      ? 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300'
                      : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300'"
                  >
                    {{ s.employee_type }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.department_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.designation_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.phone ?? '—' }}</td>
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
                <td class="px-4 py-3">
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

    <StaffForm
      v-model="modalOpen"
      :staff="editing"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import StaffForm from '../components/StaffForm.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getStaffs } from '../services/staffService.js';

const toast = useToast();

const loading = ref(true);
const error = ref(null);
const staffs = ref([]);
const modalOpen = ref(false);
const editing = ref(null);
const filterType = ref('');

async function fetch() {
  loading.value = true;
  error.value = null;
  try {
    staffs.value = await getStaffs(
      filterType.value ? { employee_type: filterType.value } : {}
    );
  } catch {
    error.value = 'Failed to load staff.';
    staffs.value = [];
  } finally {
    loading.value = false;
  }
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
  toast.success(editing.value ? 'Staff updated successfully.' : 'Staff created successfully.');
}

onMounted(fetch);
</script>
