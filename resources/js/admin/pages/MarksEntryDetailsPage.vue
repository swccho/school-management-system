<template>
  <PageContainer
    title="Marks entry review"
    description="Review entered marks for the selected exam, class, section and subject."
  >
    <template #actions>
      <router-link
        to="/admin/marks-entry"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        ← Back to list
      </router-link>
    </template>
    <div class="space-y-4">
      <div v-if="loading" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading…
      </div>
      <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-300">
        {{ error }}
      </div>
      <template v-else>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h2 class="mb-2 text-sm font-semibold text-zinc-800 dark:text-zinc-200">Context</h2>
          <dl class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
            <div>
              <dt class="text-zinc-500 dark:text-zinc-400">Exam</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ detail?.exam?.name ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-zinc-500 dark:text-zinc-400">Class</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ detail?.class?.name ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-zinc-500 dark:text-zinc-400">Section</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ detail?.section?.name ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-zinc-500 dark:text-zinc-400">Subject</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ detail?.subject?.name ?? '—' }}</dd>
            </div>
          </dl>
          <div class="mt-4">
            <router-link
              :to="editLink"
              class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
            >
              Edit marks →
            </router-link>
          </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
          <div v-if="!detail?.entries?.length" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
            No marks entered for this context.
          </div>
          <div v-else class="sidenav-scroll overflow-x-auto">
            <table class="w-full min-w-[400px]">
              <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-800">
                  <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Student</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Admission / Roll</th>
                  <th
                    v-for="comp in componentColumns"
                    :key="comp"
                    class="px-2 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400"
                  >
                    {{ comp }}
                  </th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Total</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="e in detail.entries"
                  :key="e.id"
                  class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
                >
                  <td class="px-4 py-2.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ e.student_name ?? '—' }}</td>
                  <td class="px-4 py-2.5 text-sm text-zinc-600 dark:text-zinc-400">{{ e.admission_no ?? e.roll_no ?? '—' }}</td>
                  <td
                    v-for="comp in componentColumns"
                    :key="comp"
                    class="px-2 py-2.5 text-sm text-zinc-600 dark:text-zinc-400"
                  >
                    {{ getItemObtained(e, comp) }}
                  </td>
                  <td class="px-4 py-2.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ e.total_obtained ?? '—' }}</td>
                  <td class="px-4 py-2.5">
                    <span
                      class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                      :class="e.status === 'submitted' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                    >
                      {{ e.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>
  </PageContainer>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getMarksEntryDetail } from '../services/marksEntryService.js';

const route = useRoute();
const loading = ref(true);
const error = ref(null);
const detail = ref(null);

const queryParams = computed(() => ({
  exam_id: route.query.exam_id,
  class_id: route.query.class_id,
  section_id: route.query.section_id,
  subject_id: route.query.subject_id,
}));

const componentColumns = computed(() => {
  const first = detail.value?.entries?.[0];
  if (!first?.items?.length) return [];
  return first.items.map((i) => i.component_name || 'Marks');
});

const editLink = computed(() => {
  const q = new URLSearchParams();
  if (detail.value?.exam?.id) q.set('exam_id', detail.value.exam.id);
  if (detail.value?.class?.id) q.set('class_id', detail.value.class.id);
  if (detail.value?.subject?.id) q.set('subject_id', detail.value.subject.id);
  if (detail.value?.section?.id) q.set('section_id', detail.value.section.id);
  return `/admin/marks-entry/create?${q.toString()}`;
});

function getItemObtained(entry, componentName) {
  const item = (entry.items || []).find((i) => (i.component_name || 'Marks') === componentName);
  return item?.obtained_marks ?? '—';
}

async function fetch() {
  const { exam_id, class_id, section_id, subject_id } = queryParams.value;
  if (!exam_id || !class_id || !subject_id) {
    error.value = 'Missing exam, class or subject in URL.';
    loading.value = false;
    return;
  }
  loading.value = true;
  error.value = null;
  try {
    detail.value = await getMarksEntryDetail({
      exam_id: Number(exam_id),
      class_id: Number(class_id),
      section_id: section_id ? Number(section_id) : undefined,
      subject_id: Number(subject_id),
    });
  } catch {
    error.value = 'Failed to load marks entry detail.';
    detail.value = null;
  } finally {
    loading.value = false;
  }
}

watch(queryParams, () => fetch(), { deep: true });
onMounted(() => fetch());
</script>
