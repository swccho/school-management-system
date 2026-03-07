<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="emit('close')"
    >
      <div class="absolute inset-0 bg-zinc-900/50" aria-hidden="true" />
      <div
        class="relative w-full max-w-2xl rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 max-h-[90vh] overflow-y-auto"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="isEdit ? 'edit-student-title' : 'add-student-title'"
      >
        <h2
          :id="isEdit ? 'edit-student-title' : 'add-student-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit student' : 'Add student' }}
        </h2>

        <form class="mt-4 space-y-6" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <section class="space-y-4">
            <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Basic information</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="student-admission" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Admission no</label>
                <input
                  id="student-admission"
                  v-model="form.admission_no"
                  type="text"
                  required
                  class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                />
              </div>
              <div>
                <label for="student-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                <select
                  id="student-status"
                  v-model="form.status"
                  class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="archived">Archived</option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="student-first-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">First name</label>
                <input
                  id="student-first-name"
                  v-model="form.first_name"
                  type="text"
                  required
                  class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                />
              </div>
              <div>
                <label for="student-last-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Last name</label>
                <input
                  id="student-last-name"
                  v-model="form.last_name"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="student-gender" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Gender</label>
                <select
                  id="student-gender"
                  v-model="form.gender"
                  class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                >
                  <option value="">—</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div>
                <DatePicker
                  id="student-dob"
                  v-model="form.date_of_birth"
                  label="Date of birth"
                  clearable
                />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="student-blood" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Blood group</label>
                <input
                  id="student-blood"
                  v-model="form.blood_group"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                />
              </div>
              <div>
                <label for="student-religion" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Religion</label>
                <input
                  id="student-religion"
                  v-model="form.religion"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Photo</label>
              <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Photo upload will be available in a future release.</p>
            </div>
          </section>

          <section class="space-y-4">
            <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Contact information</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="student-phone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Phone</label>
                <input
                  id="student-phone"
                  v-model="form.phone"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                />
              </div>
              <div>
                <label for="student-email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                <input
                  id="student-email"
                  v-model="form.email"
                  type="email"
                  class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                />
              </div>
            </div>
            <div>
              <label for="student-present-address" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Present address</label>
              <textarea
                id="student-present-address"
                v-model="form.present_address"
                rows="2"
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
            <div>
              <label for="student-permanent-address" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Permanent address</label>
              <textarea
                id="student-permanent-address"
                v-model="form.permanent_address"
                rows="2"
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
          </section>

          <section v-if="!isEdit" class="space-y-4">
            <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Guardian (optional)</h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Add a primary guardian now or link one later from the student details page.</p>
            <div class="rounded-lg border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
              <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                  <label for="guardian-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Guardian name</label>
                  <input
                    id="guardian-name"
                    v-model="primaryGuardian.name"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                  />
                </div>
                <div>
                  <label for="guardian-relation" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Relation</label>
                  <select
                    id="guardian-relation"
                    v-model="primaryGuardian.relation_type"
                    class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                  >
                    <option value="">—</option>
                    <option value="father">Father</option>
                    <option value="mother">Mother</option>
                    <option value="guardian">Guardian</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div>
                  <label for="guardian-phone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Phone</label>
                  <input
                    id="guardian-phone"
                    v-model="primaryGuardian.phone"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                  />
                </div>
                <div>
                  <label for="guardian-email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                  <input
                    id="guardian-email"
                    v-model="primaryGuardian.email"
                    type="email"
                    class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                  />
                </div>
                <div>
                  <label for="guardian-occupation" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Occupation</label>
                  <input
                    id="guardian-occupation"
                    v-model="primaryGuardian.occupation"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                  />
                </div>
                <div>
                  <label for="guardian-address" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Address</label>
                  <input
                    id="guardian-address"
                    v-model="primaryGuardian.address"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                  />
                </div>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Or link existing guardian</label>
              <SearchableSelect
                id="student-existing-guardian"
                v-model="form.existing_guardian_id"
                label=""
                :options="guardians"
                label-key="name"
                value-key="id"
                placeholder="Select guardian…"
                search-placeholder="Search guardians…"
                clearable
              />
            </div>
          </section>

          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
              @click="emit('close')"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-50 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
              :disabled="saving"
            >
              <span v-if="saving">{{ isEdit ? 'Saving…' : 'Creating…' }}</span>
              <span v-else>{{ isEdit ? 'Save' : 'Create' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import DatePicker from '../../shared/components/form/DatePicker.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { attachGuardian, createStudent, updateStudent } from '../services/studentService.js';
import { getGuardians } from '../services/guardianService.js';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  student: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.student);

const form = reactive({
  admission_no: '',
  first_name: '',
  last_name: '',
  gender: '',
  date_of_birth: '',
  blood_group: '',
  religion: '',
  phone: '',
  email: '',
  present_address: '',
  permanent_address: '',
  status: 'active',
  existing_guardian_id: null,
});

const primaryGuardian = reactive({
  name: '',
  relation_type: '',
  phone: '',
  email: '',
  occupation: '',
  address: '',
});

const saving = ref(false);
const formError = ref(null);
const guardians = ref([]);

async function loadGuardians() {
  try {
    guardians.value = await getGuardians({ status: 'active' });
  } catch {
    guardians.value = [];
  }
}

function resetForm() {
  form.admission_no = '';
  form.first_name = '';
  form.last_name = '';
  form.gender = '';
  form.date_of_birth = '';
  form.blood_group = '';
  form.religion = '';
  form.phone = '';
  form.email = '';
  form.present_address = '';
  form.permanent_address = '';
  form.status = 'active';
  form.existing_guardian_id = null;
  primaryGuardian.name = '';
  primaryGuardian.relation_type = '';
  primaryGuardian.phone = '';
  primaryGuardian.email = '';
  primaryGuardian.occupation = '';
  primaryGuardian.address = '';
  formError.value = null;
}

function assign(s) {
  if (!s) {
    resetForm();
    return;
  }
  form.admission_no = s.admission_no ?? '';
  form.first_name = s.first_name ?? '';
  form.last_name = s.last_name ?? '';
  form.gender = s.gender ?? '';
  form.date_of_birth = s.date_of_birth ?? '';
  form.blood_group = s.blood_group ?? '';
  form.religion = s.religion ?? '';
  form.phone = s.phone ?? '';
  form.email = s.email ?? '';
  form.present_address = s.present_address ?? '';
  form.permanent_address = s.permanent_address ?? '';
  form.status = s.status ?? 'active';
  form.existing_guardian_id = null;
  formError.value = null;
}

watch(
  () => [props.modelValue, props.student],
  async () => {
    if (props.modelValue) {
      if (!isEdit.value) await loadGuardians();
      assign(props.student);
      if (isEdit.value) {
        primaryGuardian.name = '';
        primaryGuardian.relation_type = '';
        primaryGuardian.phone = '';
        primaryGuardian.email = '';
        primaryGuardian.occupation = '';
        primaryGuardian.address = '';
      }
    }
  },
  { immediate: true }
);

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  const payload = {
    admission_no: form.admission_no,
    first_name: form.first_name,
    last_name: form.last_name || null,
    gender: form.gender || null,
    date_of_birth: form.date_of_birth || null,
    blood_group: form.blood_group || null,
    religion: form.religion || null,
    phone: form.phone || null,
    email: form.email || null,
    present_address: form.present_address || null,
    permanent_address: form.permanent_address || null,
    status: form.status,
  };
  try {
    if (isEdit.value) {
      await updateStudent(props.student.id, payload);
    } else {
      const hasInlineGuardian = primaryGuardian.name && primaryGuardian.name.trim() !== '';
      const existingGuardianId = form.existing_guardian_id ? Number(form.existing_guardian_id) : null;
      if (hasInlineGuardian) {
        payload.primary_guardian = {
          name: primaryGuardian.name.trim(),
          relation_type: primaryGuardian.relation_type || null,
          phone: primaryGuardian.phone || null,
          email: primaryGuardian.email || null,
          occupation: primaryGuardian.occupation || null,
          address: primaryGuardian.address || null,
        };
      }
      const result = await createStudent(payload);
      if (existingGuardianId && result.student) {
        await attachGuardian(result.student.id, {
          guardian_id: existingGuardianId,
          is_primary: !hasInlineGuardian,
          can_receive_sms: true,
          can_receive_email: true,
          can_login: false,
        });
      }
    }
    emit('saved');
    emit('update:modelValue', false);
    emit('close');
  } catch (err) {
    const msg = err.response?.data?.message;
    const errors = err.response?.data?.errors;
    formError.value = msg || (errors ? Object.values(errors).flat().join(' ') : 'Something went wrong.');
  } finally {
    saving.value = false;
  }
}
</script>
