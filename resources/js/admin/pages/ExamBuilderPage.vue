<template>
  <PageContainer
    :title="isEdit ? 'Edit exam' : 'Create exam'"
    :description="isEdit ? 'Update exam configuration and subject marks.' : 'Set up a new exam: basic info, target classes, and subject marks.'"
  >
    <div class="space-y-6">
      <ExamBasicInfoForm
        v-model="basicInfo"
        :disabled="isEdit"
      />
      <ExamClassConfigSection v-model="classConfigs" />
      <ExamSubjectConfigTable
        v-model="subjectConfigs"
        :class-options="classOptions"
        :subject-options="subjectOptions"
      />

      <div v-if="submitError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
        {{ submitError }}
      </div>
      <div class="flex gap-2">
        <button
          type="button"
          class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
          :disabled="submitting"
          @click="submit"
        >
          {{ submitting ? 'Saving…' : (isEdit ? 'Update exam' : 'Create exam') }}
        </button>
        <router-link
          :to="isEdit ? `/admin/exams/${route.params.id}` : '/admin/exams'"
          class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
        >
          Cancel
        </router-link>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import ExamBasicInfoForm from '../components/ExamBasicInfoForm.vue';
import ExamClassConfigSection from '../components/ExamClassConfigSection.vue';
import ExamSubjectConfigTable from '../components/ExamSubjectConfigTable.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getExam, createExam, updateExam, getClasses, getSubjects } from '../services/examService.js';

const route = useRoute();
const toast = useToast();
const router = useRouter();
const isEdit = computed(() => !!route.params.id && route.params.id !== 'create');

const basicInfo = ref({
  academic_session_id: null,
  exam_type_id: null,
  name: '',
  code: '',
  start_date: '',
  end_date: '',
  result_publish_date: '',
  status: 'draft',
  description: '',
});
const classConfigs = ref([{ class_id: null, section_id: null, status: 'active' }]);
const subjectConfigs = ref([]);
const classOptions = ref([]);
const subjectOptions = ref([]);
const submitting = ref(false);
const submitError = ref(null);

function buildPayload() {
  const class_configs = classConfigs.value
    .filter((c) => c.class_id)
    .map((c) => ({
      id: c.id,
      class_id: c.class_id,
      section_id: c.section_id || null,
      status: c.status || 'active',
    }));
  const subject_configs = subjectConfigs.value
    .filter((s) => s.class_id && s.subject_id)
    .map((s) => ({
      id: s.id,
      class_id: s.class_id,
      subject_id: s.subject_id,
      full_marks: s.full_marks ?? 100,
      pass_marks: s.pass_marks ?? 33,
      theory_marks: s.theory_marks ?? null,
      practical_marks: s.practical_marks ?? null,
      oral_marks: s.oral_marks ?? null,
      has_practical: s.has_practical ?? false,
      sort_order: s.sort_order ?? null,
      mark_components: s.mark_components ?? [],
    }));
  return {
    academic_session_id: basicInfo.value.academic_session_id,
    exam_type_id: basicInfo.value.exam_type_id,
    name: basicInfo.value.name,
    code: basicInfo.value.code || null,
    start_date: basicInfo.value.start_date,
    end_date: basicInfo.value.end_date,
    result_publish_date: basicInfo.value.result_publish_date || null,
    status: basicInfo.value.status,
    description: basicInfo.value.description || null,
    class_configs,
    subject_configs,
  };
}

async function submit() {
  const payload = buildPayload();
  if (payload.class_configs.length === 0) {
    submitError.value = 'Add at least one target class.';
    return;
  }
  if (payload.subject_configs.length === 0) {
    submitError.value = 'Add at least one subject configuration.';
    return;
  }
  submitting.value = true;
  submitError.value = null;
  try {
    if (isEdit.value) {
      await updateExam(route.params.id, payload);
      toast.success('Exam updated successfully.');
      router.push(`/admin/exams/${route.params.id}`);
    } else {
      const res = await createExam(payload);
      toast.success('Exam created successfully.');
      router.push(`/admin/exams/${res.exam.id}`);
    }
  } catch (err) {
    const msg = err.response?.data?.message ?? 'Failed to save exam.';
    submitError.value = msg;
    toast.error(msg);
  } finally {
    submitting.value = false;
  }
}

onMounted(async () => {
  const [classes, subjects] = await Promise.all([getClasses(), getSubjects()]);
  classOptions.value = classes;
  subjectOptions.value = subjects;

  if (isEdit.value) {
    const exam = await getExam(route.params.id);
    basicInfo.value = {
      academic_session_id: exam.academic_session_id,
      exam_type_id: exam.exam_type_id,
      name: exam.name,
      code: exam.code ?? '',
      start_date: exam.start_date ?? '',
      end_date: exam.end_date ?? '',
      result_publish_date: exam.result_publish_date ?? '',
      status: exam.status ?? 'draft',
      description: exam.description ?? '',
    };
    classConfigs.value = (exam.class_configs || []).map((c) => ({
      id: c.id,
      class_id: c.class_id,
      section_id: c.section_id ?? null,
      status: c.status ?? 'active',
    }));
    if (classConfigs.value.length === 0) {
      classConfigs.value = [{ class_id: null, section_id: null, status: 'active' }];
    }
    subjectConfigs.value = (exam.subject_configs || []).map((s) => ({
      id: s.id,
      class_id: s.class_id,
      subject_id: s.subject_id,
      full_marks: s.full_marks,
      pass_marks: s.pass_marks,
      theory_marks: s.theory_marks,
      practical_marks: s.practical_marks,
      oral_marks: s.oral_marks,
      has_practical: s.has_practical ?? false,
      sort_order: s.sort_order,
      mark_components: s.mark_components ?? [],
    }));
  }
});
</script>
