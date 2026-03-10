<template>
  <PageContainer title="Students" description="View students in your assigned classes.">
    <div class="mb-4 flex flex-wrap gap-4">
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Class · Section</label>
        <select v-model="classSectionValue" class="mt-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
          <option value="">Select class and section</option>
          <option v-for="a in uniqueClassSections" :key="a.class_id + '-' + a.section_id" :value="a.class_id + '-' + a.section_id">{{ a.class_name }} · {{ a.section_name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Search (name or roll)</label>
        <input v-model="searchTerm" type="text" placeholder="Search…" class="mt-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" @keyup.enter="fetchStudents" />
      </div>
      <button type="button" class="self-end rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900" :disabled="!classSectionValue" @click="fetchStudents">Load students</button>
    </div>
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="students.length === 0 && hasFetched" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">No students in this class/section.</div>
    <div v-else-if="students.length > 0" class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <table class="w-full min-w-[400px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Roll</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Admission no</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in students" :key="s.id" class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0">
            <td class="px-4 py-3 text-sm">{{ s.roll_no }}</td>
            <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ s.full_name }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.admission_no }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { useAuthStore } from '../stores/authStore.js';
import { getStudents } from '../services/studentService.js';

const authStore = useAuthStore();
const loading = ref(false);
const hasFetched = ref(false);
const students = ref([]);
const classSectionValue = ref('');
const searchTerm = ref('');

const assignments = computed(() => authStore.assignments ?? []);
const uniqueClassSections = computed(() => {
  const as = assignments.value;
  const seen = new Set();
  return as.filter((a) => {
    const k = a.class_id + '-' + a.section_id;
    if (seen.has(k)) return false;
    seen.add(k);
    return true;
  });
});

async function fetchStudents() {
  const v = classSectionValue.value;
  if (!v) return;
  const [classId, sectionId] = v.split('-').map(Number);
  loading.value = true;
  hasFetched.value = true;
  try {
    const params = { class_id: classId, section_id: sectionId };
    if (searchTerm.value.trim()) params.search = searchTerm.value.trim();
    students.value = await getStudents(params);
  } catch {
    students.value = [];
  } finally {
    loading.value = false;
  }
}
</script>
