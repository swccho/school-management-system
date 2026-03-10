<template>
  <PageContainer
    title="Academic assignment"
    :description="student ? `${student.full_name} (${student.admission_no ?? '—'})` : 'Loading…'"
  >
    <template #actions>
      <router-link
        :to="{ name: 'student-details', params: { id: route.params.id } }"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        Back to student
      </router-link>
    </template>

    <div v-if="loadingStudent" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading student…
    </div>
    <div v-else-if="studentError" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
      {{ studentError }}
    </div>
    <div v-else-if="student" class="space-y-6">
      <section
        v-if="currentAssignment"
        class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900"
      >
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Current academic assignment</h3>
        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Session</dt>
            <dd class="mt-0.5 text-sm text-zinc-900 dark:text-zinc-100">
              {{ currentAssignment.academic_session_name ?? '—' }}
            </dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Class</dt>
            <dd class="mt-0.5 text-sm text-zinc-900 dark:text-zinc-100">
              {{ currentAssignment.class_name ?? '—' }}
            </dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Section</dt>
            <dd class="mt-0.5 text-sm text-zinc-900 dark:text-zinc-100">
              {{ currentAssignment.section_name ?? '—' }}
            </dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Roll number</dt>
            <dd class="mt-0.5 text-sm text-zinc-900 dark:text-zinc-100">
              {{ currentAssignment.roll_no ?? '—' }}
            </dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Status</dt>
            <dd class="mt-0.5">
              <span
                class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                :class="currentAssignment.status === 'active'
                  ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                  : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
              >
                {{ currentAssignment.status }}
              </span>
            </dd>
          </div>
          <div class="sm:col-span-2">
            <button
              type="button"
              class="rounded-lg border border-zinc-900 bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
              @click="startEditCurrent"
            >
              Edit current assignment
            </button>
          </div>
        </dl>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
          {{ editingAssignment ? 'Edit assignment' : (currentAssignment ? 'Add another assignment' : 'Assign to session, class & section') }}
        </h3>
        <div class="mt-4">
          <StudentAcademicAssignmentForm
            :student-id="student.id"
            :assignment="editingAssignment"
            @saved="onFormSaved"
            @cancel="editingAssignment = null"
          />
        </div>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Assignment history</h3>
        <div v-if="loadingHistory" class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">
          Loading…
        </div>
        <div v-else-if="!history.length" class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">
          No assignments yet.
        </div>
        <div v-else class="mt-4 overflow-x-auto">
          <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead>
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Session</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Class</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Section</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Roll no</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Status</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Created</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
              <tr v-for="a in history" :key="a.id" class="text-sm">
                <td class="whitespace-nowrap px-4 py-2 text-zinc-900 dark:text-zinc-100">{{ a.academic_session_name ?? '—' }}</td>
                <td class="whitespace-nowrap px-4 py-2 text-zinc-700 dark:text-zinc-300">{{ a.class_name ?? '—' }}</td>
                <td class="whitespace-nowrap px-4 py-2 text-zinc-700 dark:text-zinc-300">{{ a.section_name ?? '—' }}</td>
                <td class="whitespace-nowrap px-4 py-2 text-zinc-700 dark:text-zinc-300">{{ a.roll_no ?? '—' }}</td>
                <td class="whitespace-nowrap px-4 py-2">
                  <span
                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="a.status === 'active'
                      ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                      : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                  >
                    {{ a.status }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ formatDate(a.created_at) }}</td>
                <td class="whitespace-nowrap px-4 py-2 text-right">
                  <button
                    type="button"
                    class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="editAssignment(a)"
                  >
                    Edit
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import StudentAcademicAssignmentForm from '../components/StudentAcademicAssignmentForm.vue';
import { getStudent } from '../services/studentService.js';
import { getAssignments, getAssignment } from '../services/studentAcademicAssignmentService.js';
import { useToast } from '../../shared/composables/useToast.js';

const toast = useToast();
const route = useRoute();

const studentId = computed(() => route.params.id);
const loadingStudent = ref(true);
const studentError = ref(null);
const student = ref(null);
const loadingHistory = ref(false);
const history = ref([]);
const editingAssignment = ref(null);

const currentAssignment = computed(() => {
  if (!student.value?.current_academic_assignment) return null;
  return student.value.current_academic_assignment;
});

async function fetchStudent() {
  if (!studentId.value) return;
  loadingStudent.value = true;
  studentError.value = null;
  try {
    student.value = await getStudent(studentId.value);
  } catch {
    studentError.value = 'Failed to load student.';
    student.value = null;
  } finally {
    loadingStudent.value = false;
  }
}

async function fetchHistory() {
  if (!studentId.value) return;
  loadingHistory.value = true;
  try {
    const list = await getAssignments({ student_id: studentId.value });
    history.value = Array.isArray(list) ? list : [];
  } catch {
    history.value = [];
  } finally {
    loadingHistory.value = false;
  }
}

async function startEditCurrent() {
  if (!currentAssignment.value?.id) return;
  try {
    const full = await getAssignment(currentAssignment.value.id);
    editingAssignment.value = full;
  } catch {
    editingAssignment.value = { ...currentAssignment.value };
  }
}

async function editAssignment(a) {
  try {
    const full = await getAssignment(a.id);
    editingAssignment.value = full;
  } catch {
    editingAssignment.value = { ...a };
  }
}

function formatDate(iso) {
  if (!iso) return '—';
  try {
    return new Date(iso).toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  } catch {
    return iso;
  }
}

function onFormSaved() {
  toast.success('Assignment saved successfully.');
  editingAssignment.value = null;
  fetchStudent();
  fetchHistory();
}

watch(studentId, () => {
  fetchStudent();
  fetchHistory();
}, { immediate: true });
</script>
