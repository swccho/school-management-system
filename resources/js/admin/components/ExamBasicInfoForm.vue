<template>
  <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
    <p class="mb-4 text-sm font-medium text-zinc-700 dark:text-zinc-300">
      Exam basic information
    </p>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div>
        <SearchableSelect
          id="exam-session"
          v-model="form.academic_session_id"
          label="Academic session"
          :options="sessionOptions"
          label-key="name"
          value-key="id"
          placeholder="Select session…"
          search-placeholder="Search…"
          required
          clearable
          :disabled="disabled"
          @update:model-value="emitUpdate"
        />
      </div>
      <div>
        <SearchableSelect
          id="exam-type"
          v-model="form.exam_type_id"
          label="Exam type"
          :options="examTypeOptions"
          label-key="name"
          value-key="id"
          placeholder="Select type…"
          search-placeholder="Search…"
          required
          clearable
          :disabled="disabled"
          @update:model-value="emitUpdate"
        />
      </div>
      <div>
        <label for="exam-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</label>
        <input
          id="exam-name"
          v-model="form.name"
          type="text"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          placeholder="e.g. Midterm Exam 2026"
          required
          @input="emitUpdate"
        />
      </div>
      <div>
        <label for="exam-code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Code (optional)</label>
        <input
          id="exam-code"
          v-model="form.code"
          type="text"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @input="emitUpdate"
        />
      </div>
      <div>
        <DatePicker
          id="exam-start"
          v-model="form.start_date"
          label="Start date"
          required
          @change="emitUpdate"
        />
      </div>
      <div>
        <DatePicker
          id="exam-end"
          v-model="form.end_date"
          label="End date"
          required
          @change="emitUpdate"
        />
      </div>
      <div>
        <DatePicker
          id="exam-result-date"
          v-model="form.result_publish_date"
          label="Result publish date (optional)"
          clearable
          @change="emitUpdate"
        />
      </div>
      <div>
        <label for="exam-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
        <select
          id="exam-status"
          v-model="form.status"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @change="emitUpdate"
        >
          <option value="draft">Draft</option>
          <option value="active">Active</option>
          <option value="completed">Completed</option>
          <option value="archived">Archived</option>
        </select>
      </div>
      <div class="sm:col-span-2 lg:col-span-3">
        <label for="exam-desc" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description (optional)</label>
        <textarea
          id="exam-desc"
          v-model="form.description"
          rows="2"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @input="emitUpdate"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import DatePicker from '../../shared/components/form/DatePicker.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { getAcademicSessions } from '../services/examService.js';
import { getExamTypes } from '../services/examService.js';

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const form = ref({
  academic_session_id: props.modelValue.academic_session_id ?? null,
  exam_type_id: props.modelValue.exam_type_id ?? null,
  name: props.modelValue.name ?? '',
  code: props.modelValue.code ?? '',
  start_date: props.modelValue.start_date ?? '',
  end_date: props.modelValue.end_date ?? '',
  result_publish_date: props.modelValue.result_publish_date ?? '',
  status: props.modelValue.status ?? 'draft',
  description: props.modelValue.description ?? '',
});

const sessionOptions = ref([]);
const examTypeOptions = ref([]);

async function loadOptions() {
  try {
    const [sessions, types] = await Promise.all([getAcademicSessions(), getExamTypes()]);
    sessionOptions.value = sessions;
    examTypeOptions.value = types;
  } catch {
    sessionOptions.value = [];
    examTypeOptions.value = [];
  }
}

function emitUpdate() {
  emit('update:modelValue', { ...form.value });
}

watch(() => props.modelValue, (v) => {
  if (v) {
    form.value.academic_session_id = v.academic_session_id ?? null;
    form.value.exam_type_id = v.exam_type_id ?? null;
    form.value.name = v.name ?? '';
    form.value.code = v.code ?? '';
    form.value.start_date = v.start_date ?? '';
    form.value.end_date = v.end_date ?? '';
    form.value.result_publish_date = v.result_publish_date ?? '';
    form.value.status = v.status ?? 'draft';
    form.value.description = v.description ?? '';
  }
}, { deep: true });

onMounted(loadOptions);
</script>
