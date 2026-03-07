<template>
  <PageContainer
    title="Grade Scales"
    description="Manage grading schemes used for result calculation (letter grade and GPA)."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreate"
      >
        Add Grade Scale
      </button>
    </template>

    <div class="space-y-4">
      <div v-if="showForm" class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="mb-4 text-sm font-semibold text-zinc-800 dark:text-zinc-200">
          {{ editingId ? 'Edit Grade Scale' : 'New Grade Scale' }}
        </h3>
        <GradeScaleForm
          ref="formRef"
          :model-value="formData"
          :submit-label="editingId ? 'Update' : 'Create'"
          :show-cancel="true"
          @submit="onSubmit"
          @cancel="closeForm"
        />
      </div>

      <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          Loading…
        </div>
        <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
          {{ error }}
        </div>
        <div v-else-if="list.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          No grade scales yet. Add one to use for result generation.
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[400px]">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800">
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Default</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Ranges</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in list"
                :key="item.id"
                class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
              >
                <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ item.name }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                  <span v-if="item.is_default" class="text-emerald-600 dark:text-emerald-400">Yes</span>
                  <span v-else>—</span>
                </td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ item.status }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                  {{ item.items?.length ?? 0 }} range(s)
                </td>
                <td class="px-4 py-3">
                  <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="openEdit(item)"
                  >
                    Edit
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import GradeScaleForm from '../components/GradeScaleForm.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getGradeScales, getGradeScale, createGradeScale, updateGradeScale } from '../services/gradeScaleService.js';

const toast = useToast();

const loading = ref(true);
const error = ref(null);
const list = ref([]);
const showForm = ref(false);
const editingId = ref(null);
const formData = ref({ name: '', is_default: false, status: 'active', items: [] });
const formRef = ref(null);

async function fetch() {
  loading.value = true;
  error.value = null;
  try {
    list.value = await getGradeScales();
  } catch {
    error.value = 'Failed to load grade scales.';
    list.value = [];
  } finally {
    loading.value = false;
  }
}

function openCreate() {
  editingId.value = null;
  formData.value = { name: '', is_default: false, status: 'active', items: [{ min_mark: 80, max_mark: 100, letter_grade: 'A+', grade_point: 5, remarks: '' }] };
  showForm.value = true;
}

async function openEdit(item) {
  editingId.value = item.id;
  try {
    const scale = await getGradeScale(item.id);
    formData.value = {
      name: scale.name,
      is_default: scale.is_default,
      status: scale.status,
      items: scale.items?.length ? scale.items.map((i) => ({ min_mark: i.min_mark, max_mark: i.max_mark, letter_grade: i.letter_grade, grade_point: i.grade_point, remarks: i.remarks ?? '' })) : [],
    };
    showForm.value = true;
  } catch {
    error.value = 'Failed to load grade scale.';
  }
}

function closeForm() {
  showForm.value = false;
  editingId.value = null;
}

async function onSubmit(payload) {
  error.value = null;
  try {
    if (editingId.value) {
      await updateGradeScale(editingId.value, payload);
    } else {
      await createGradeScale(payload);
    }
    closeForm();
    await fetch();
    toast.success('Grade scale saved successfully.');
  } catch (e) {
    const msg = e.response?.data?.message || 'Failed to save grade scale.';
    error.value = msg;
    toast.error(msg);
  }
}

onMounted(() => fetch());
</script>
