<template>
  <PageContainer
    title="Student details"
    :description="student ? student.full_name : 'Loading…'"
  >
    <template #actions>
      <button
        v-if="student"
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openEdit"
      >
        Edit
      </button>
      <router-link
        :to="{ name: 'students' }"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        Back to list
      </router-link>
    </template>

    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading student…
    </div>
    <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else-if="student" class="space-y-6">
      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Basic information</h3>
        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Admission no</dt>
            <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ student.admission_no }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Name</dt>
            <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ student.full_name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Gender</dt>
            <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ student.gender ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Date of birth</dt>
            <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ student.date_of_birth_formatted ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Blood group</dt>
            <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ student.blood_group ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Status</dt>
            <dd class="mt-0.5">
              <span
                class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                :class="student.status === 'active'
                  ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                  : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
              >
                {{ student.status }}
              </span>
            </dd>
          </div>
        </dl>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Contact information</h3>
        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Phone</dt>
            <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ student.phone ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Email</dt>
            <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ student.email ?? '—' }}</dd>
          </div>
          <div class="sm:col-span-2">
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Present address</dt>
            <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ student.present_address || '—' }}</dd>
          </div>
          <div class="sm:col-span-2">
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Permanent address</dt>
            <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ student.permanent_address || '—' }}</dd>
          </div>
        </dl>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Guardians</h3>
        <div v-if="!student.guardians || student.guardians.length === 0" class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">
          No guardians linked. Edit the student to add one.
        </div>
        <ul v-else class="mt-4 space-y-3">
          <li
            v-for="g in student.guardians"
            :key="g.id"
            class="flex items-center justify-between rounded-lg border border-zinc-100 py-3 px-4 dark:border-zinc-800"
          >
            <div>
              <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ g.name }}</span>
              <span v-if="g.relation_type" class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">({{ g.relation_type }})</span>
              <span
                v-if="g.is_primary"
                class="ml-2 inline-flex rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-800 dark:bg-sky-900/40 dark:text-sky-300"
              >
                Primary
              </span>
            </div>
            <div class="text-sm text-zinc-600 dark:text-zinc-400">
              <span v-if="g.phone">{{ g.phone }}</span>
              <span v-if="g.phone && g.email"> · </span>
              <span v-if="g.email">{{ g.email }}</span>
              <span v-if="!g.phone && !g.email">—</span>
            </div>
          </li>
        </ul>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Academic assignment</h3>
        <div v-if="student.current_academic_assignment" class="mt-4 space-y-3">
          <dl class="grid gap-2 sm:grid-cols-2">
            <div>
              <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Session</dt>
              <dd class="mt-0.5 text-sm text-zinc-900 dark:text-zinc-100">
                {{ student.current_academic_assignment.academic_session_name ?? '—' }}
              </dd>
            </div>
            <div>
              <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Class</dt>
              <dd class="mt-0.5 text-sm text-zinc-900 dark:text-zinc-100">
                {{ student.current_academic_assignment.class_name ?? '—' }}
              </dd>
            </div>
            <div>
              <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Section</dt>
              <dd class="mt-0.5 text-sm text-zinc-900 dark:text-zinc-100">
                {{ student.current_academic_assignment.section_name ?? '—' }}
              </dd>
            </div>
            <div>
              <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Roll number</dt>
              <dd class="mt-0.5 text-sm text-zinc-900 dark:text-zinc-100">
                {{ student.current_academic_assignment.roll_no ?? '—' }}
              </dd>
            </div>
          </dl>
          <router-link
            :to="{ name: 'student-academic-assignment', params: { id: student.id } }"
            class="inline-block rounded-lg border border-zinc-900 bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
          >
            Manage Academic Assignment
          </router-link>
        </div>
        <div v-else class="mt-4">
          <p class="text-sm text-zinc-500 dark:text-zinc-400">No current academic assignment.</p>
          <router-link
            :to="{ name: 'student-academic-assignment', params: { id: student.id } }"
            class="mt-2 inline-block rounded-lg border border-zinc-900 bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
          >
            Assign Academic Info
          </router-link>
        </div>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Attendance &amp; results</h3>
        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Attendance and result summaries will be available in a future release.</p>
      </section>
    </div>

    <StudentForm
      v-model="editModalOpen"
      :student="student"
      @saved="onSaved"
      @close="editModalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import StudentForm from '../components/StudentForm.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getStudent } from '../services/studentService.js';

const toast = useToast();

const route = useRoute();
const loading = ref(true);
const error = ref(null);
const student = ref(null);
const editModalOpen = ref(false);

const studentId = computed(() => route.params.id);

async function fetch() {
  if (!studentId.value) return;
  loading.value = true;
  error.value = null;
  try {
    student.value = await getStudent(studentId.value);
  } catch {
    error.value = 'Failed to load student.';
    student.value = null;
  } finally {
    loading.value = false;
  }
}

function openEdit() {
  editModalOpen.value = true;
}

function onSaved() {
  fetch();
  toast.success('Student updated successfully.');
}

watch(studentId, fetch, { immediate: true });
</script>
