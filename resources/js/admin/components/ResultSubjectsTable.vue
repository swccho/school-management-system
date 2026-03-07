<template>
  <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[400px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">Full</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">Obtained</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Grade</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">GPA</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in subjectDetails"
            :key="row.subject_id"
            class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
          >
            <td class="px-4 py-2.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ row.subject_name ?? '—' }}</td>
            <td class="px-4 py-2.5 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ row.full_marks }}</td>
            <td class="px-4 py-2.5 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ row.obtained_marks }}</td>
            <td class="px-4 py-2.5 text-sm text-zinc-600 dark:text-zinc-400">{{ row.grade_letter ?? '—' }}</td>
            <td class="px-4 py-2.5 text-sm text-zinc-600 dark:text-zinc-400">{{ row.grade_point ?? '—' }}</td>
            <td class="px-4 py-2.5">
              <span
                class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                :class="row.pass_status === 'pass' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'"
              >
                {{ row.pass_status === 'pass' ? 'Pass' : 'Fail' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  subjectDetails: { type: Array, default: () => [] },
});
</script>
