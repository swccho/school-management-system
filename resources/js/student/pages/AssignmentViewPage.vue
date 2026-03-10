<template>
  <PageContainer title="Assignment" :description="assignment?.subject_name ? `${assignment.subject_name} · Due ${assignment.due_date || '—'}` : 'View and submit.'">
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading…
    </div>
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else-if="assignment" class="space-y-6">
      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ assignment.title }}</h2>
        <dl class="mt-3 grid gap-x-4 gap-y-2 text-sm sm:grid-cols-2">
          <div><dt class="text-zinc-500 dark:text-zinc-400">Subject</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ assignment.subject_name ?? '—' }}</dd></div>
          <div><dt class="text-zinc-500 dark:text-zinc-400">Teacher</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ assignment.teacher_name ?? '—' }}</dd></div>
          <div><dt class="text-zinc-500 dark:text-zinc-400">Due date</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ assignment.due_date ?? '—' }}</dd></div>
          <div v-if="assignment.attachment_url">
            <dt class="text-zinc-500 dark:text-zinc-400">Attachment</dt>
            <dd>
              <a
                :href="attachmentDownloadUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="font-medium text-blue-600 hover:underline dark:text-blue-400"
              >
                Download attachment
              </a>
            </dd>
          </div>
        </dl>
        <div v-if="assignment.description" class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-800">
          <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Instructions</h3>
          <div class="mt-2 whitespace-pre-wrap text-sm text-zinc-600 dark:text-zinc-400">{{ assignment.description }}</div>
        </div>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Your submission</h3>
        <div v-if="assignment.submission" class="mt-3 space-y-2 rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
          <p class="text-sm text-zinc-600 dark:text-zinc-400">Submitted on {{ formatDate(assignment.submission.submitted_at) }}</p>
          <p v-if="assignment.submission.note" class="text-sm text-zinc-700 dark:text-zinc-300">{{ assignment.submission.note }}</p>
          <a
            v-if="assignment.submission.file_url"
            :href="assignment.submission.file_url"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-block text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
          >
            View submitted file
          </a>
          <div v-if="assignment.submission.status === 'graded'" class="mt-2 border-t border-zinc-200 pt-2 dark:border-zinc-700">
            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Grade: {{ assignment.submission.grade ?? '—' }}</p>
            <p v-if="assignment.submission.feedback" class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ assignment.submission.feedback }}</p>
          </div>
        </div>

        <div v-if="canSubmit" class="mt-4 space-y-4">
          <p v-if="submitSuccess" class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
            {{ submitSuccess }}
          </p>
          <p v-if="submitError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
            {{ submitError }}
          </p>
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
              <label for="file" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">File (optional)</label>
              <input
                id="file"
                type="file"
                accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png"
                class="mt-1 block w-full text-sm text-zinc-600 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-zinc-700 dark:text-zinc-400 dark:file:bg-zinc-700 dark:file:text-zinc-200"
                @change="file = $event.target.files?.[0]"
              />
              <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">PDF, DOC, DOCX, TXT, JPG, PNG. Max 10MB.</p>
            </div>
            <div>
              <label for="note" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Note (optional)</label>
              <textarea
                id="note"
                v-model="note"
                rows="3"
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                placeholder="Add a comment..."
              />
            </div>
            <button
              type="submit"
              class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
              :disabled="submitLoading"
            >
              <span v-if="submitLoading">Submitting…</span>
              <span v-else>{{ assignment.submission ? 'Resubmit' : 'Submit' }}</span>
            </button>
          </form>
        </div>
        <p v-else-if="!assignment.submission && !canSubmit" class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
          This assignment is no longer accepting submissions (deadline passed or closed).
        </p>
      </section>

      <router-link
        to="/student/assignments"
        class="inline-block text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
      >
        ← Back to assignments
      </router-link>
    </div>
    <div v-else class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Assignment not found.
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getAssignment, submitAssignment } from '../services/homeworkService.js';

const route = useRoute();
const loading = ref(true);
const error = ref(null);
const assignment = ref(null);
const file = ref(null);
const note = ref('');
const submitLoading = ref(false);
const submitSuccess = ref(null);
const submitError = ref(null);

const canSubmit = computed(() => {
  const a = assignment.value;
  if (!a || a.status !== 'active') return false;
  const due = a.due_date ? new Date(a.due_date) : null;
  if (due && due < new Date()) return false;
  return true;
});

const attachmentDownloadUrl = computed(() => {
  const a = assignment.value;
  if (!a?.attachment_url) return '#';
  return `/api/student/homework/${a.id}/attachment`;
});

function formatDate(iso) {
  if (!iso) return '—';
  return new Date(iso).toLocaleString();
}

async function fetchAssignment() {
  const id = route.params.id;
  if (!id) return;
  loading.value = true;
  error.value = null;
  try {
    assignment.value = await getAssignment(id);
    note.value = assignment.value?.submission?.note ?? '';
  } catch (e) {
    error.value = e.response?.status === 404 ? 'Assignment not found.' : (e.response?.data?.message ?? 'Failed to load assignment.');
    assignment.value = null;
  } finally {
    loading.value = false;
  }
}

async function handleSubmit() {
  submitSuccess.value = null;
  submitError.value = null;
  submitLoading.value = true;
  try {
    const formData = new FormData();
    if (file.value) formData.append('file', file.value);
    if (note.value) formData.append('note', note.value);
    await submitAssignment(route.params.id, formData);
    submitSuccess.value = 'Submission saved successfully.';
    file.value = null;
    await fetchAssignment();
  } catch (e) {
    const msg = e.response?.data?.message ?? e.response?.data?.errors
      ? Object.values(e.response.data.errors).flat().join(' ')
      : 'Submission failed.';
    submitError.value = msg;
  } finally {
    submitLoading.value = false;
  }
}

onMounted(fetchAssignment);
watch(() => route.params.id, fetchAssignment);
</script>
