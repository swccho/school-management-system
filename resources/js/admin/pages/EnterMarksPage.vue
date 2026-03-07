<template>
  <PageContainer
    title="Enter Marks"
    description="Select exam, class, section and subject, then load students and enter marks."
  >
    <template #actions>
      <router-link
        to="/admin/marks-entry"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        ← Back to list
      </router-link>
    </template>
    <div class="space-y-6">
      <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <p class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Context</p>
        <MarksEntryFilterForm
          v-model="context"
          :show-subject="true"
        />
        <div class="mt-4 flex items-center gap-3">
          <button
            type="button"
            :disabled="!canLoad"
            class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-50 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            @click="loadStudents"
          >
            Load students
          </button>
          <span v-if="!canLoad" class="text-sm text-zinc-500 dark:text-zinc-400">
            Select exam, class and subject to load students.
          </span>
        </div>
      </div>

      <template v-if="loaded">
        <div v-if="loadError" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-300">
          {{ loadError }}
        </div>
        <template v-else>
          <div class="flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
              {{ students.length }} student(s). {{ subjectConfig ? `Max marks: ${subjectConfig.full_marks}` : '' }}
            </p>
            <div class="flex gap-2">
              <select
                v-model="submitStatus"
                class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              >
                <option value="draft">Save as draft</option>
                <option value="submitted">Submit</option>
              </select>
              <button
                type="button"
                :disabled="saving || entries.length === 0"
                class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-50 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                @click="save"
              >
                {{ saving ? 'Saving…' : 'Save marks' }}
              </button>
            </div>
          </div>
          <MarksEntryTable
            :students="students"
            :mark-components="markComponents"
            :full-marks="fullMarks"
            :existing-entries="existingEntries"
            @update:entries="entries = $event"
          />
        </template>
      </template>
    </div>
  </PageContainer>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import MarksEntryFilterForm from '../components/MarksEntryFilterForm.vue';
import MarksEntryTable from '../components/MarksEntryTable.vue';
import { useToast } from '../../shared/composables/useToast.js';
import {
  getEligibleStudents,
  getSubjectConfig,
  getMarksEntryDetail,
  saveMarks,
} from '../services/marksEntryService.js';

const route = useRoute();
const toast = useToast();
const context = ref({
  exam_id: null,
  class_id: null,
  section_id: null,
  subject_id: null,
});
const students = ref([]);
const subjectConfig = ref(null);
const existingEntries = ref([]);
const entries = ref([]);
const loaded = ref(false);
const loadError = ref(null);
const saving = ref(false);
const submitStatus = ref('draft');

const canLoad = computed(() =>
  context.value.exam_id && context.value.class_id && context.value.subject_id
);

const markComponents = computed(() => subjectConfig.value?.mark_components ?? []);
const fullMarks = computed(() => subjectConfig.value?.full_marks ?? 100);

function applyQuery() {
  const q = route.query;
  if (q.exam_id) context.value.exam_id = Number(q.exam_id);
  if (q.class_id) context.value.class_id = Number(q.class_id);
  if (q.section_id) context.value.section_id = Number(q.section_id);
  if (q.subject_id) context.value.subject_id = Number(q.subject_id);
}

async function loadStudents() {
  if (!canLoad.value) return;
  loaded.value = true;
  loadError.value = null;
  students.value = [];
  subjectConfig.value = null;
  existingEntries.value = [];
  entries.value = [];
  try {
    const [studentList, config, detail] = await Promise.all([
      getEligibleStudents(context.value.exam_id, context.value.class_id, context.value.section_id || null),
      getSubjectConfig(context.value.exam_id, context.value.class_id, context.value.subject_id),
      getMarksEntryDetail({
        exam_id: context.value.exam_id,
        class_id: context.value.class_id,
        section_id: context.value.section_id || undefined,
        subject_id: context.value.subject_id,
      }),
    ]);
    if (!config) {
      loadError.value = 'Subject is not configured for this exam and class.';
      return;
    }
    subjectConfig.value = config;
    students.value = studentList.map((s) => ({
      student_id: s.student_id,
      full_name: s.full_name,
      admission_no: s.admission_no,
      roll_no: s.roll_no,
    }));
    existingEntries.value = (detail?.entries ?? []).map((e) => ({
      student_id: e.student_id,
      items: (e.items ?? []).map((i) => ({
        mark_component_id: i.mark_component_id ?? null,
        obtained_marks: i.obtained_marks ?? 0,
      })),
    }));
  } catch {
    loadError.value = 'Failed to load students or subject config.';
  }
}

async function save() {
  if (saving.value || !context.value.exam_id || !context.value.class_id || !context.value.subject_id) return;
  saving.value = true;
  try {
    await saveMarks({
      exam_id: context.value.exam_id,
      class_id: context.value.class_id,
      section_id: context.value.section_id || null,
      subject_id: context.value.subject_id,
      status: submitStatus.value,
      entries: entries.value,
    });
    toast.success('Marks saved successfully.');
    await loadStudents();
  } catch {
    loadError.value = 'Failed to save marks.';
    toast.error('Failed to save marks.');
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  applyQuery();
  if (canLoad.value) loadStudents();
});
</script>
