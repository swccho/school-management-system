<template>
  <PageContainer title="Enter Marks" :description="examName ? `Marks for ${examName}` : 'Select class/section/subject to enter marks.'">
    <div class="mb-4 flex flex-wrap gap-4">
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Class · Section · Subject</label>
        <select v-model="contextValue" class="mt-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" @change="loadMarksEntry">
          <option value="">Select context</option>
          <option v-for="c in contexts" :key="contextKey(c)" :value="contextKey(c)">{{ c.class_name }} · {{ c.section_name }} · {{ c.subject_name }}</option>
        </select>
      </div>
    </div>
    <div v-if="loadingContexts" class="py-4 text-sm text-zinc-500">Loading contexts…</div>
    <div v-else-if="loadingData" class="py-4 text-sm text-zinc-500">Loading students and marks…</div>
    <div v-else-if="!contextValue" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      Select a class/section/subject above.
    </div>
    <div v-else-if="subjectConfig" class="space-y-4">
      <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ subjectConfig.subject_name }} — Full marks: {{ subjectConfig.full_marks }}, Pass: {{ subjectConfig.pass_marks }}</p>
      <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <table class="w-full min-w-[600px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Student</th>
              <th v-for="comp in subjectConfig.mark_components" :key="comp.id" class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ comp.name }} ({{ comp.marks }})</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in entryRows" :key="row.student_id" class="border-b border-zinc-100 dark:border-zinc-800/50">
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ row.full_name }}</td>
              <td v-for="comp in subjectConfig.mark_components" :key="comp.id" class="px-4 py-2">
                <input
                  v-model.number="row.items[comp.id]"
                  type="number"
                  min="0"
                  :max="comp.marks"
                  class="w-16 rounded border border-zinc-300 px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800"
                />
              </td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ rowTotal(row) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex gap-3">
        <button type="button" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900" :disabled="saving" @click="saveDraft">
          {{ saving ? 'Saving…' : 'Save draft' }}
        </button>
        <button type="button" class="rounded-lg border border-zinc-900 px-4 py-2 text-sm font-medium dark:border-zinc-100 dark:text-zinc-100" :disabled="saving" @click="submitMarks">
          Submit marks
        </button>
      </div>
      <p v-if="message" class="text-sm" :class="messageError ? 'text-red-600 dark:text-red-400' : 'text-zinc-600 dark:text-zinc-400'">{{ message }}</p>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getMarksEntryContexts, getMarksEntry, saveMarks, submitMarks as submitMarksApi } from '../services/examService.js';

const route = useRoute();
const examId = computed(() => route.params.examId);

const contexts = ref([]);
const loadingContexts = ref(true);
const loadingData = ref(false);
const contextValue = ref('');
const subjectConfig = ref(null);
const students = ref([]);
const entriesByStudent = ref({});
const saving = ref(false);
const message = ref('');
const messageError = ref(false);
const examName = ref('');

function contextKey(c) {
  return [c.class_id, c.section_id ?? '', c.subject_id].join('-');
}

function currentContext() {
  if (!contextValue.value) return null;
  const [class_id, section_id, subject_id] = contextValue.value.split('-');
  return { class_id: Number(class_id), section_id: section_id ? Number(section_id) : null, subject_id: Number(subject_id) };
}

const entryRows = computed(() => {
  const comps = subjectConfig.value?.mark_components ?? [];
  return students.value.map((s) => {
    const entry = entriesByStudent.value[s.student_id];
    const items = {};
    comps.forEach((c) => { items[c.id] = entry?.items?.find((i) => i.mark_component_id === c.id)?.obtained_marks ?? ''; });
    return { ...s, items };
  });
});

function rowTotal(row) {
  const comps = subjectConfig.value?.mark_components ?? [];
  return comps.reduce((sum, c) => sum + (Number(row.items[c.id]) || 0), 0);
}

async function loadContexts() {
  if (!examId.value) return;
  loadingContexts.value = true;
  try {
    contexts.value = await getMarksEntryContexts(examId.value);
    if (contexts.value.length === 1) contextValue.value = contextKey(contexts.value[0]);
  } catch {
    contexts.value = [];
  } finally {
    loadingContexts.value = false;
  }
}

async function loadMarksEntry() {
  const ctx = currentContext();
  if (!ctx) {
    subjectConfig.value = null;
    students.value = [];
    entriesByStudent.value = {};
    return;
  }
  loadingData.value = true;
  message.value = '';
  try {
    const data = await getMarksEntry(examId.value, ctx);
    examName.value = data.exam?.name ?? '';
    subjectConfig.value = data.subject_config;
    students.value = data.students ?? [];
    const byStudent = {};
    (data.entries ?? []).forEach((e) => {
      byStudent[e.student_id] = e;
    });
    entriesByStudent.value = byStudent;
  } catch {
    subjectConfig.value = null;
    students.value = [];
    entriesByStudent.value = {};
  } finally {
    loadingData.value = false;
  }
}

async function saveDraft() {
  const ctx = currentContext();
  if (!ctx || !subjectConfig.value) return;
  saving.value = true;
  message.value = '';
  messageError.value = false;
  try {
    const entries = entryRows.value.map((row) => ({
      student_id: row.student_id,
      items: subjectConfig.value.mark_components.map((c) => ({
        mark_component_id: c.id,
        obtained_marks: row.items[c.id] ?? 0,
      })),
    }));
    await saveMarks(examId.value, { ...ctx, status: 'draft', entries });
    message.value = 'Draft saved.';
  } catch (e) {
    message.value = e.response?.data?.message ?? 'Failed to save.';
    messageError.value = true;
  } finally {
    saving.value = false;
  }
}

async function submitMarks() {
  const ctx = currentContext();
  if (!ctx) return;
  saving.value = true;
  message.value = '';
  messageError.value = false;
  try {
    const entries = entryRows.value.map((row) => ({
      student_id: row.student_id,
      items: subjectConfig.value.mark_components.map((c) => ({
        mark_component_id: c.id,
        obtained_marks: row.items[c.id] ?? 0,
      })),
    }));
    await saveMarks(examId.value, { ...ctx, status: 'submitted', entries });
    await submitMarksApi(examId.value, ctx);
    message.value = 'Marks submitted.';
  } catch (e) {
    message.value = e.response?.data?.message ?? 'Failed to submit.';
    messageError.value = true;
  } finally {
    saving.value = false;
  }
}

watch(examId, () => { contextValue.value = ''; loadContexts(); }, { immediate: true });
watch(contextValue, () => { if (contextValue.value) loadMarksEntry(); });

onMounted(loadContexts);
</script>
