<template>
  <PageContainer
    title="Sections"
    description="Manage class sections (e.g. Class 1–A, B, C). Sections are linked to a class and used for students, attendance, and routines."
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
        No sections yet. Add a class first, then add sections.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full min-w-[700px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Class</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Section name</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Code</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Room no.</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Capacity</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Updated</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="sec in sections"
              :key="sec.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ sec.class_name ?? '—' }}</td>
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ sec.name }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ sec.code ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ sec.room_no ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ sec.capacity ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="sec.status === 'active'
                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                    : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                >
                  {{ sec.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ sec.updated_at_formatted ?? '—' }}</td>
              <td class="px-4 py-3">
                <button
                  type="button"
                  class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  @click="openEditModal(sec)"
                >
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <SectionForm
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
import SectionForm from '../components/SectionForm.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getSections } from '../services/sectionService.js';

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

function openEditModal(sec) {
  editingSection.value = sec;
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
    sections.value = await getSections();
  } catch {
    error.value = 'Failed to load sections.';
    sections.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(fetchSections);
</script>
