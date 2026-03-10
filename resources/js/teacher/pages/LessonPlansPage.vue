<template>
  <PageContainer title="Lesson Plans" description="Create and manage lesson plans for your classes.">
    <div class="mb-4 flex flex-wrap gap-4">
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900"
        @click="showForm(null)"
      >
        Add Lesson Plan
      </button>
      <div class="flex flex-wrap items-end gap-2">
        <input v-model="filters.date_from" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
        <input v-model="filters.date_to" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
        <div>
          <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400">Class</label>
          <select v-model="filters.class_id" class="mt-1 rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
            <option value="">All</option>
            <option v-for="c in uniqueClasses" :key="c.class_id" :value="c.class_id">{{ c.class_name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400">Subject</label>
          <select v-model="filters.subject_id" class="mt-1 rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
            <option value="">All</option>
            <option v-for="s in uniqueSubjects" :key="s.subject_id" :value="s.subject_id">{{ s.subject_name }}</option>
          </select>
        </div>
        <button type="button" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600" @click="load">Apply</button>
      </div>
    </div>
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="items.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      No lesson plans in this range.
    </div>
    <div v-else class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <table class="w-full min-w-[500px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Date</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Class · Section · Subject</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="l in items" :key="l.id" class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0">
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ l.plan_date }}</td>
            <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ l.title }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ l.class_name }} · {{ l.section_name }} · {{ l.subject_name }}</td>
            <td class="px-4 py-3">
              <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="l.status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'">{{ l.status }}</span>
            </td>
            <td class="px-4 py-3 text-right">
              <button type="button" class="text-sm text-blue-600 hover:underline dark:text-blue-400" @click="showForm(l)">Edit</button>
              <button type="button" class="ml-3 text-sm text-red-600 hover:underline dark:text-red-400" @click="confirmDelete(l)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="formOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="formOpen = false">
      <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900" @click.stop>
        <h3 class="mb-4 text-lg font-semibold">{{ editingId ? 'Edit Lesson Plan' : 'Add Lesson Plan' }}</h3>
        <form @submit.prevent="submitForm">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium">Class · Section · Subject</label>
              <select v-model="form.classSectionSubject" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" :disabled="!!editingId">
                <option value="">Select</option>
                <option v-for="a in assignments" :key="a.class_id + '-' + a.section_id + '-' + a.subject_id" :value="a.class_id + '-' + a.section_id + '-' + a.subject_id">
                  {{ a.class_name }} · {{ a.section_name }} · {{ a.subject_name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium">Plan date</label>
              <input v-model="form.plan_date" type="date" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
            </div>
            <div>
              <label class="block text-sm font-medium">Title</label>
              <input v-model="form.title" type="text" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
            </div>
            <div>
              <label class="block text-sm font-medium">Content</label>
              <textarea v-model="form.content" rows="4" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium">Status</label>
              <select v-model="form.status" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                <option value="draft">Draft</option>
                <option value="completed">Completed</option>
              </select>
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
import { getLessonPlans, createLessonPlan, updateLessonPlan, deleteLessonPlan } from '../services/lessonPlanService.js';

const authStore = useAuthStore();
const loading = ref(true);
const items = ref([]);
const formOpen = ref(false);
const editingId = ref(null);
const saving = ref(false);
const formError = ref('');

const assignments = computed(() => authStore.assignments ?? []);
const uniqueClasses = computed(() => {
  const seen = new Set();
  return assignments.value.filter((a) => {
    if (seen.has(a.class_id)) return false;
    seen.add(a.class_id);
    return true;
  }).map((a) => ({ class_id: a.class_id, class_name: a.class_name }));
});
const uniqueSubjects = computed(() => {
  const seen = new Set();
  return assignments.value.filter((a) => {
    if (seen.has(a.subject_id)) return false;
    seen.add(a.subject_id);
    return true;
  }).map((a) => ({ subject_id: a.subject_id, subject_name: a.subject_name }));
});

const filters = ref({ date_from: '', date_to: '', class_id: '', subject_id: '' });
const form = ref({
  classSectionSubject: '',
  plan_date: '',
  title: '',
  content: '',
  status: 'draft',
});

async function load() {
  loading.value = true;
  try {
    const params = {};
    if (filters.value.date_from) params.date_from = filters.value.date_from;
    if (filters.value.date_to) params.date_to = filters.value.date_to;
    if (filters.value.class_id) params.class_id = filters.value.class_id;
    if (filters.value.subject_id) params.subject_id = filters.value.subject_id;
    items.value = await getLessonPlans(params);
  } catch {
    items.value = [];
  } finally {
    loading.value = false;
  }
}

function showForm(record) {
  editingId.value = record?.id ?? null;
  if (record) {
    form.value = {
      classSectionSubject: [record.class_id, record.section_id, record.subject_id].filter(Boolean).join('-'),
      plan_date: record.plan_date,
      title: record.title,
      content: record.content ?? '',
      status: record.status ?? 'draft',
    };
  } else {
    form.value = { classSectionSubject: '', plan_date: '', title: '', content: '', status: 'draft' };
  }
  formError.value = '';
  formOpen.value = true;
}

async function submitForm() {
  formError.value = '';
  const [class_id, section_id, subject_id] = form.value.classSectionSubject.split('-').map(Number);
  saving.value = true;
  try {
    if (editingId.value) {
      await updateLessonPlan(editingId.value, { title: form.value.title, content: form.value.content, plan_date: form.value.plan_date, status: form.value.status });
    } else {
      await createLessonPlan({ class_id, section_id, subject_id, title: form.value.title, content: form.value.content, plan_date: form.value.plan_date, status: form.value.status });
    }
    formOpen.value = false;
    load();
  } catch (e) {
    formError.value = e.response?.data?.message ?? 'Failed to save.';
  } finally {
    saving.value = false;
  }
}

function confirmDelete(l) {
  if (!window.confirm(`Delete "${l.title}"?`)) return;
  deleteLessonPlan(l.id).then(() => load()).catch(() => {});
}

onMounted(load);
</script>
