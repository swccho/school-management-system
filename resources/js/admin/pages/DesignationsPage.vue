<template>
  <PageContainer
    title="Designations"
    description="Manage job designations (e.g. Principal, Assistant Teacher, Accountant). Link to departments and use in staff records."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreate"
      >
        Add Designation
      </button>
    </template>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading designations…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="designations.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        No designations yet. Add one to get started.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full min-w-[500px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Department</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Code</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="d in designations"
              :key="d.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ d.name }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ d.department_name ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ d.code ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="d.status === 'active'
                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                    : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                >
                  {{ d.status }}
                </span>
              </td>
              <td class="px-4 py-3">
                <button
                  type="button"
                  class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  @click="openEdit(d)"
                >
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <DesignationForm
      v-model="modalOpen"
      :designation="editing"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import DesignationForm from '../components/DesignationForm.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getDesignations } from '../services/designationService.js';

const toast = useToast();

const loading = ref(true);
const error = ref(null);
const designations = ref([]);
const modalOpen = ref(false);
const editing = ref(null);

function openCreate() {
  editing.value = null;
  modalOpen.value = true;
}

function openEdit(d) {
  editing.value = d;
  modalOpen.value = true;
}

function onSaved() {
  fetch();
  toast.success(editing.value ? 'Designation updated successfully.' : 'Designation created successfully.');
}

async function fetch() {
  loading.value = true;
  error.value = null;
  try {
    designations.value = await getDesignations();
  } catch {
    error.value = 'Failed to load designations.';
    designations.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(fetch);
</script>
