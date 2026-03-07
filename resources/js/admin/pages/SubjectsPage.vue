<template>
  <PageContainer
    title="Subjects"
    description="Manage the subject catalog (e.g. Bangla, English, Mathematics). Used for class assignment, routines, exams, and results."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreateModal"
      >
        Add Subject
      </button>
    </template>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading subjects…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="subjects.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        No subjects yet. Add one to get started.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full min-w-[700px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Code</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Short name</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Full / Pass</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Flags</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="subj in subjects"
              :key="subj.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ subj.name }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ subj.code ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ subj.short_name ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  v-if="subj.type"
                  class="inline-flex rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300"
                >
                  {{ subj.type }}
                </span>
                <span v-else class="text-sm text-zinc-400 dark:text-zinc-500">—</span>
              </td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                <template v-if="subj.full_marks != null || subj.pass_marks != null">
                  {{ subj.full_marks ?? '—' }} / {{ subj.pass_marks ?? '—' }}
                </template>
                <span v-else>—</span>
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-if="subj.is_optional"
                    class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"
                  >
                    Optional
                  </span>
                  <span
                    v-if="subj.has_practical"
                    class="inline-flex rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-800 dark:bg-sky-900/40 dark:text-sky-300"
                  >
                    Practical
                  </span>
                  <span v-if="!subj.is_optional && !subj.has_practical" class="text-sm text-zinc-400 dark:text-zinc-500">—</span>
                </div>
              </td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="subj.status === 'active'
                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                    : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                >
                  {{ subj.status }}
                </span>
              </td>
              <td class="px-4 py-3">
                <button
                  type="button"
                  class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  @click="openEditModal(subj)"
                >
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <SubjectForm
      v-model="modalOpen"
      :subject="editingSubject"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import SubjectForm from '../components/SubjectForm.vue';
import { getSubjects } from '../services/subjectService.js';

const loading = ref(true);
const error = ref(null);
const subjects = ref([]);
const modalOpen = ref(false);
const editingSubject = ref(null);

function openCreateModal() {
  editingSubject.value = null;
  modalOpen.value = true;
}

function openEditModal(subj) {
  editingSubject.value = subj;
  modalOpen.value = true;
}

function onSaved() {
  fetchSubjects();
}

async function fetchSubjects() {
  loading.value = true;
  error.value = null;
  try {
    subjects.value = await getSubjects();
  } catch {
    error.value = 'Failed to load subjects.';
    subjects.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(fetchSubjects);
</script>
