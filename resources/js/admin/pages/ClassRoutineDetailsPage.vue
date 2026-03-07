<template>
  <PageContainer
    title="Routine details"
    :description="routine ? `${routine.class_name} – ${routine.section_name}${routine.title ? ` (${routine.title})` : ''}` : ''"
  >
    <template #actions>
      <router-link
        :to="`/admin/routines/${route.params.id}/edit`"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        Edit routine
      </router-link>
      <router-link
        to="/admin/routines"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        Back to list
      </router-link>
    </template>

    <div class="space-y-6">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <template v-else-if="routine">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-sm text-zinc-600 dark:text-zinc-400">
            <span class="font-medium text-zinc-800 dark:text-zinc-200">Session:</span> {{ routine.academic_session_name ?? '—' }}
            · <span class="font-medium text-zinc-800 dark:text-zinc-200">Effective:</span> {{ routine.effective_from_formatted ?? '—' }}
            <span v-if="routine.effective_to_formatted ?? routine.effective_to"> – {{ routine.effective_to_formatted ?? routine.effective_to ?? '—' }}</span>
            · <span class="font-medium text-zinc-800 dark:text-zinc-200">Status:</span> {{ routine.status }}
          </p>
        </div>

        <div class="space-y-4">
          <div
            v-for="day in dayOrder"
            :key="day"
            class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"
          >
            <div class="border-b border-zinc-200 px-4 py-2 dark:border-zinc-800">
              <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                {{ dayLabel(day) }}
              </h3>
            </div>
            <div class="sidenav-scroll overflow-x-auto">
              <table class="w-full min-w-[500px]">
                <thead>
                  <tr class="border-b border-zinc-200 dark:border-zinc-800">
                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Period</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Time</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Subject</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Teacher</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Room</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="item in itemsByDay(day)"
                    :key="item.id || `${day}-${item.period_no}`"
                    class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
                  >
                    <td class="px-4 py-2.5 text-sm text-zinc-900 dark:text-zinc-100">{{ item.period_no }}</td>
                    <td class="px-4 py-2.5 text-sm text-zinc-600 dark:text-zinc-400">{{ item.start_time }} – {{ item.end_time }}</td>
                    <td class="px-4 py-2.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ item.subject_name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-sm text-zinc-600 dark:text-zinc-400">{{ item.teacher_name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-sm text-zinc-600 dark:text-zinc-400">{{ item.room_label ?? '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-if="itemsByDay(day).length === 0" class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
              No periods
            </p>
          </div>
        </div>
      </template>
    </div>
  </PageContainer>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getClassRoutine, dayLabel, DAYS_OF_WEEK } from '../services/classRoutineService.js';

const route = useRoute();
const loading = ref(true);
const error = ref(null);
const routine = ref(null);

const dayOrder = DAYS_OF_WEEK;

function itemsByDay(day) {
  if (!routine.value?.items) return [];
  return routine.value.items
    .filter((i) => i.day_of_week === day)
    .sort((a, b) => a.period_no - b.period_no);
}

onMounted(async () => {
  try {
    routine.value = await getClassRoutine(route.params.id);
  } catch {
    error.value = 'Failed to load routine.';
  } finally {
    loading.value = false;
  }
});
</script>
