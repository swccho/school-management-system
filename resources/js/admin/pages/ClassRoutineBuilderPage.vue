<template>
  <PageContainer
    :title="isEdit ? 'Edit routine' : 'Create routine'"
    :description="isEdit ? 'Update timetable for this class and section.' : 'Set up a new timetable by session, class, section and day-wise periods.'"
  >
    <div class="space-y-6">
      <ClassRoutineHeaderForm
        v-model="header"
        :disabled="isEdit"
      />
      <div v-if="header.class_id" class="space-y-4">
        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
          Periods by day
        </p>
        <div class="space-y-4">
          <ClassRoutineDayTable
            v-for="day in DAYS"
            :key="day"
            :day-of-week="day"
            :items="itemsByDay[day] || []"
            :subject-options="subjectOptions"
            :teacher-options="teacherOptions"
            :editable="true"
            @update:items="(payload) => setDayItems(day, payload)"
          />
        </div>
      </div>

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
          {{ submitting ? 'Saving…' : (isEdit ? 'Update routine' : 'Create routine') }}
        </button>
        <router-link
          :to="isEdit ? `/admin/routines/${route.params.id}` : '/admin/routines'"
          class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
        >
          Cancel
        </router-link>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import ClassRoutineHeaderForm from '../components/ClassRoutineHeaderForm.vue';
import ClassRoutineDayTable from '../components/ClassRoutineDayTable.vue';
import { useToast } from '../../shared/composables/useToast.js';
import {
  getClassRoutine,
  createClassRoutine,
  updateClassRoutine,
  getAcademicSessions,
  getClasses,
  getSections,
  getSubjects,
  getTeachers,
  DAYS_OF_WEEK as DAYS,
} from '../services/classRoutineService.js';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const isEdit = computed(() => !!route.params.id && route.params.id !== 'create');

const header = ref({
  academic_session_id: null,
  class_id: null,
  section_id: null,
  title: '',
  effective_from: '',
  effective_to: '',
  status: 'active',
});

const itemsByDay = reactive(
  Object.fromEntries(DAYS.map((d) => [d, []]))
);

const subjectOptions = ref([]);
const teacherOptions = ref([]);
const submitting = ref(false);
const submitError = ref(null);

function setDayItems(day, payload) {
  itemsByDay[day] = payload;
}

function allItems() {
  return DAYS.flatMap((day) => itemsByDay[day] || []);
}

async function submit() {
  const items = allItems();
  if (items.length === 0) {
    submitError.value = 'Add at least one period.';
    return;
  }
  const itemPayload = items.map((i) => ({
    id: i.id,
    day_of_week: i.day_of_week,
    period_no: i.period_no,
    start_time: i.start_time,
    end_time: i.end_time,
    subject_id: i.subject_id,
    teacher_id: i.teacher_id || null,
    room_label: i.room_label || null,
    remarks: i.remarks || null,
  }));
  submitting.value = true;
  submitError.value = null;
  try {
    if (isEdit.value) {
      await updateClassRoutine(route.params.id, {
        title: header.value.title || null,
        effective_from: header.value.effective_from,
        effective_to: header.value.effective_to || null,
        status: header.value.status,
        items: itemPayload,
      });
      toast.success('Routine updated successfully.');
      router.push(`/admin/routines/${route.params.id}`);
    } else {
      const res = await createClassRoutine({
        academic_session_id: header.value.academic_session_id,
        class_id: header.value.class_id,
        section_id: header.value.section_id,
        title: header.value.title || null,
        effective_from: header.value.effective_from,
        effective_to: header.value.effective_to || null,
        status: header.value.status,
        items: itemPayload,
      });
      toast.success('Routine created successfully.');
      router.push(`/admin/routines/${res.class_routine.id}`);
    }
  } catch (err) {
    const msg = err.response?.data?.message ?? 'Failed to save routine.';
    submitError.value = msg;
    toast.error(msg);
  } finally {
    submitting.value = false;
  }
}

onMounted(async () => {
  const [subjects, teachers] = await Promise.all([getSubjects(), getTeachers()]);
  subjectOptions.value = subjects;
  teacherOptions.value = teachers;

  if (isEdit.value) {
    const routine = await getClassRoutine(route.params.id);
    header.value = {
      academic_session_id: routine.academic_session_id,
      class_id: routine.class_id,
      section_id: routine.section_id,
      title: routine.title ?? '',
      effective_from: routine.effective_from ?? '',
      effective_to: routine.effective_to ?? '',
      status: routine.status ?? 'active',
    };
    const byDay = {};
    DAYS.forEach((d) => { byDay[d] = []; });
    (routine.items || []).forEach((i) => {
      if (!byDay[i.day_of_week]) byDay[i.day_of_week] = [];
      byDay[i.day_of_week].push({
        id: i.id,
        day_of_week: i.day_of_week,
        period_no: i.period_no,
        start_time: i.start_time,
        end_time: i.end_time,
        subject_id: i.subject_id,
        teacher_id: i.teacher_id,
        room_label: i.room_label,
        remarks: i.remarks,
      });
    });
    DAYS.forEach((d) => { itemsByDay[d] = byDay[d] ?? []; });
  }
});
</script>
