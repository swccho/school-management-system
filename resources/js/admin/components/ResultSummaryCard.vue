<template>
  <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
      <div>
        <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total Marks</dt>
        <dd class="mt-0.5 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ totalMarks }}</dd>
      </div>
      <div>
        <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Obtained</dt>
        <dd class="mt-0.5 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ obtainedMarks }}</dd>
      </div>
      <div>
        <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">GPA</dt>
        <dd class="mt-0.5 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ gpa ?? '—' }}</dd>
      </div>
      <div>
        <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Grade</dt>
        <dd class="mt-0.5 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ letterGrade ?? '—' }}</dd>
      </div>
    </div>
    <div class="mt-3 flex flex-wrap items-center gap-2">
      <span
        class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
        :class="passStatus === 'pass' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'"
      >
        {{ passStatus === 'pass' ? 'Pass' : 'Fail' }}
      </span>
      <span v-if="meritPosition" class="text-sm text-zinc-600 dark:text-zinc-400">
        Merit: {{ meritPosition }}
      </span>
      <span
        v-if="publishedStatus"
        class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300"
      >
        Published
      </span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  totalMarks: { type: [Number, String], default: 0 },
  obtainedMarks: { type: [Number, String], default: 0 },
  gpa: { type: [Number, String], default: null },
  letterGrade: { type: String, default: null },
  passStatus: { type: String, default: 'fail' },
  meritPosition: { type: Number, default: null },
  publishedStatus: { type: Boolean, default: false },
});
</script>
