<template>
  <PageContainer
    title="Classes"
    description="Manage academic classes (e.g. Play, Nursery, KG, Class 1–10). Used for sections, subjects, and student placement."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreateModal"
      >
        Add Class
      </button>
    </template>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading classes…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="classes.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        No classes yet. Add one to get started.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full min-w-[600px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Code</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Numeric level</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Updated</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="cls in classes"
              :key="cls.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ cls.name }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ cls.code ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ cls.numeric_level ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="cls.status === 'active'
                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                    : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                >
                  {{ cls.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ cls.updated_at_formatted ?? '—' }}</td>
              <td class="px-4 py-3">
                <button
                  type="button"
                  class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  @click="openEditModal(cls)"
                >
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ClassForm
      v-model="modalOpen"
      :class-item="editingClass"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import ClassForm from '../components/ClassForm.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getClasses } from '../services/classService.js';

const toast = useToast();

const loading = ref(true);
const error = ref(null);
const classes = ref([]);
const modalOpen = ref(false);
const editingClass = ref(null);

function openCreateModal() {
  editingClass.value = null;
  modalOpen.value = true;
}

function openEditModal(cls) {
  editingClass.value = cls;
  modalOpen.value = true;
}

function onSaved() {
  fetchClasses();
  toast.success(editingClass.value ? 'Class updated successfully.' : 'Class created successfully.');
}

async function fetchClasses() {
  loading.value = true;
  error.value = null;
  try {
    classes.value = await getClasses();
  } catch {
    error.value = 'Failed to load classes.';
    classes.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(fetchClasses);
</script>
