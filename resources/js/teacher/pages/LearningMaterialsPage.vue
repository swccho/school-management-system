<template>
  <PageContainer title="Learning Materials" description="Upload and manage learning materials for your classes.">
    <div class="mb-4 flex flex-wrap items-end gap-3">
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900"
        @click="showForm(null)"
      >
        Upload material
      </button>
      <div class="flex flex-wrap items-end gap-3">
        <div class="flex flex-col gap-1">
          <label for="filter-class" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Class</label>
          <select id="filter-class" v-model="filters.class_id" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
            <option value="">All</option>
            <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="flex flex-col gap-1">
          <label for="filter-section" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Section</label>
          <select id="filter-section" v-model="filters.section_id" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
            <option value="">All</option>
            <option v-for="s in sectionOptions" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div class="flex flex-col gap-1">
          <label for="filter-subject" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Subject</label>
          <select id="filter-subject" v-model="filters.subject_id" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
            <option value="">All</option>
            <option v-for="s in subjectOptions" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <button type="button" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600" @click="applyFilters">Apply</button>
        <button type="button" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600" @click="clearFilters">Clear</button>
      </div>
    </div>
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="items.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      No learning materials yet.
    </div>
    <div v-else class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <table class="w-full min-w-[500px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Class · Section · Subject</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">File</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in items" :key="m.id" class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0">
            <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ m.title }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ m.class_name }} · {{ m.section_name }} · {{ m.subject_name }}</td>
            <td class="px-4 py-3 text-sm">
              <a v-if="m.file_url || m.file_path" :href="m.file_url || downloadUrl(m.file_path)" target="_blank" rel="noopener" class="text-blue-600 hover:underline dark:text-blue-400">{{ m.file_name || 'Download' }}</a>
              <span v-else class="text-zinc-400">—</span>
            </td>
            <td class="px-4 py-3">
              <div class="flex flex-wrap justify-end gap-2">
                <button type="button" class="min-h-[44px] rounded-lg px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20" @click="showForm(m)">Edit</button>
                <button type="button" class="min-h-[44px] rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20" @click="confirmDelete(m)">Delete</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="formOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="formOpen = false">
      <div class="w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900" @click.stop>
        <h3 class="mb-4 text-lg font-semibold">{{ editingId ? 'Edit material' : 'Upload material' }}</h3>
        <form @submit.prevent="submitForm">
          <div class="space-y-4">
            <div v-if="!editingId">
              <label class="block text-sm font-medium">Class · Section · Subject</label>
              <select v-model="form.classSectionSubject" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                <option value="">Select</option>
                <option v-for="a in assignments" :key="a.class_id + '-' + a.section_id + '-' + a.subject_id" :value="a.class_id + '-' + a.section_id + '-' + a.subject_id">
                  {{ a.class_name }} · {{ a.section_name }} · {{ a.subject_name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium">Title</label>
              <input v-model="form.title" type="text" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
            </div>
            <div>
              <label class="block text-sm font-medium">Description</label>
              <textarea v-model="form.description" rows="2" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium">{{ editingId ? 'New file (optional)' : 'File' }}</label>
              <input type="file" class="mt-1 w-full text-sm" :required="!editingId" @change="form.file = $event.target.files?.[0]" />
            </div>
          </div>
          <div class="mt-4 flex gap-3">
            <button type="submit" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm text-white dark:bg-zinc-100 dark:text-zinc-900" :disabled="saving">{{ saving ? 'Saving…' : 'Save' }}</button>
            <button type="button" class="rounded-lg border px-4 py-2 text-sm dark:border-zinc-600" @click="formOpen = false">Cancel</button>
          </div>
          <p v-if="formError" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ formError }}</p>
        </form>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { useAuthStore } from '../stores/authStore.js';
import { getLearningMaterials, getLearningMaterialAssignmentOptions, createLearningMaterial, updateLearningMaterial, deleteLearningMaterial } from '../services/learningMaterialService.js';

const authStore = useAuthStore();
const assignmentOptionsList = ref([]);
const assignments = computed(() => assignmentOptionsList.value);

const classOptions = computed(() => {
  const seen = new Set();
  return (assignments.value || []).filter((a) => {
    const k = a.class_id;
    if (seen.has(k)) return false;
    seen.add(k);
    return true;
  }).map((a) => ({ id: a.class_id, name: a.class_name }));
});
const sectionOptions = computed(() => {
  const seen = new Set();
  return (assignments.value || []).filter((a) => {
    const k = a.section_id;
    if (seen.has(k)) return false;
    seen.add(k);
    return true;
  }).map((a) => ({ id: a.section_id, name: a.section_name }));
});
const subjectOptions = computed(() => {
  const seen = new Set();
  return (assignments.value || []).filter((a) => {
    const k = a.subject_id;
    if (seen.has(k)) return false;
    seen.add(k);
    return true;
  }).map((a) => ({ id: a.subject_id, name: a.subject_name }));
});

const loading = ref(true);
const items = ref([]);
const formOpen = ref(false);
const editingId = ref(null);
const saving = ref(false);
const formError = ref('');
const filters = ref({ class_id: '', section_id: '', subject_id: '' });

const form = ref({
  classSectionSubject: '',
  title: '',
  description: '',
  file: null,
});

function downloadUrl(path) {
  return `/storage/${path}`;
}

function applyFilters() {
  load();
}

function clearFilters() {
  filters.value = { class_id: '', section_id: '', subject_id: '' };
  load();
}

async function load() {
  loading.value = true;
  try {
    const params = {};
    if (filters.value.class_id) params.class_id = filters.value.class_id;
    if (filters.value.section_id) params.section_id = filters.value.section_id;
    if (filters.value.subject_id) params.subject_id = filters.value.subject_id;
    items.value = await getLearningMaterials(params);
  } catch {
    items.value = [];
  } finally {
    loading.value = false;
  }
}

function showForm(record) {
  editingId.value = record?.id ?? null;
  if (record) {
    form.value = { title: record.title, description: record.description ?? '', file: null };
  } else {
    form.value = { classSectionSubject: '', title: '', description: '', file: null };
  }
  formError.value = '';
  formOpen.value = true;
}

async function submitForm() {
  formError.value = '';
  saving.value = true;
  try {
    if (editingId.value) {
      await updateLearningMaterial(editingId.value, { title: form.value.title, description: form.value.description, file: form.value.file });
    } else {
      const [class_id, section_id, subject_id] = form.value.classSectionSubject.split('-').map(Number);
      await createLearningMaterial({ class_id, section_id, subject_id, title: form.value.title, description: form.value.description, file: form.value.file });
    }
    formOpen.value = false;
    load();
  } catch (e) {
    formError.value = e.response?.data?.message ?? 'Failed to save.';
  } finally {
    saving.value = false;
  }
}

function confirmDelete(m) {
  if (!window.confirm(`Delete "${m.title}"?`)) return;
  deleteLearningMaterial(m.id).then(() => load()).catch(() => {});
}

async function loadAssignmentsIfNeeded() {
  assignmentOptionsList.value = authStore.assignments ?? [];
  if (assignmentOptionsList.value.length === 0) {
    try {
      assignmentOptionsList.value = await getLearningMaterialAssignmentOptions();
    } catch {
      assignmentOptionsList.value = [];
    }
  }
}

onMounted(() => {
  loadAssignmentsIfNeeded();
  load();
});
</script>
