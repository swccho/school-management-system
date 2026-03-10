<template>
  <PageContainer
    title="Homepage Sections"
    description="Manage homepage content sections. Toggle visibility and reorder."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreateModal"
      >
        Add Section
      </button>
    </template>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading sections…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="sections.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        No homepage sections yet. Add one to get started.
      </div>
      <div v-else class="sidenav-scroll overflow-x-auto">
        <table class="w-full min-w-[600px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Section key</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Visible</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Sort order</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="section in sections"
              :key="section.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ section.section_key }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ section.title ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="section.is_visible ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                >
                  {{ section.is_visible ? 'Yes' : 'No' }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ section.sort_order }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="section.status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                >
                  {{ section.status }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap items-center gap-2">
                  <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="openEditModal(section)"
                  >
                    Edit
                  </button>
                  <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="toggleVisibility(section)"
                  >
                    {{ section.is_visible ? 'Hide' : 'Show' }}
                  </button>
                  <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  <button
                    type="button"
                    class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                    @click="confirmDelete(section)"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <HomepageSectionForm
      v-model="modalOpen"
      :section="editingSection"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import HomepageSectionForm from '../components/HomepageSectionForm.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import { getList, deleteSection, toggleVisibility as apiToggleVisibility } from '../services/homepageSectionService.js';

const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

const loading = ref(true);
const error = ref(null);
const sections = ref([]);
const modalOpen = ref(false);
const editingSection = ref(null);

function openCreateModal() {
  editingSection.value = null;
  modalOpen.value = true;
}

function openEditModal(section) {
  editingSection.value = section;
  modalOpen.value = true;
}

function onSaved() {
  fetchSections();
  toast.success(editingSection.value ? 'Section updated successfully.' : 'Section created successfully.');
}

async function fetchSections() {
  loading.value = true;
  error.value = null;
  try {
    sections.value = await getList();
  } catch {
    error.value = 'Failed to load sections.';
    sections.value = [];
  } finally {
    loading.value = false;
  }
}

async function confirmDelete(section) {
  const confirmed = await openConfirmation({
    title: 'Confirm deletion',
    message: 'Are you sure you want to delete this section?',
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await deleteSection(section.id);
    await fetchSections();
    closeConfirmation();
    toast.success('Section deleted successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to delete section.');
  } finally {
    setConfirmationLoading(false);
  }
}

async function toggleVisibility(section) {
  try {
    await apiToggleVisibility(section.id);
    await fetchSections();
    toast.success(section.is_visible ? 'Section hidden.' : 'Section visible.');
  } catch {
    toast.error('Failed to toggle visibility.');
  }
}

onMounted(() => {
  fetchSections();
});
</script>
