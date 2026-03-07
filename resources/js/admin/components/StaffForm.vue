<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="emit('close')"
    >
      <div class="absolute inset-0 bg-zinc-900/50" aria-hidden="true" />
      <div
        class="sidenav-scroll relative w-full max-w-2xl rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 max-h-[95vh] overflow-y-auto"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="isEdit ? 'edit-staff-title' : 'add-staff-title'"
      >
        <h2
          :id="isEdit ? 'edit-staff-title' : 'add-staff-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit staff' : 'Add staff' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div>
            <label for="staff-type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Employee type <span class="text-red-500">*</span></label>
            <select
              id="staff-type"
              v-model="form.employee_type"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="teacher">Teacher</option>
              <option value="staff">Staff</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="staff-first-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">First name <span class="text-red-500">*</span></label>
              <input
                id="staff-first-name"
                v-model="form.first_name"
                type="text"
                required
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
            <div>
              <label for="staff-last-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Last name</label>
              <input
                id="staff-last-name"
                v-model="form.last_name"
                type="text"
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="staff-gender" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Gender</label>
              <select
                id="staff-gender"
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
                id="staff-dob"
                v-model="form.date_of_birth"
                label="Date of birth"
                clearable
              />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="staff-phone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Phone</label>
              <input
                id="staff-phone"
                v-model="form.phone"
                type="text"
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
            <div>
              <label for="staff-email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
              <input
                id="staff-email"
                v-model="form.email"
                type="email"
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
          </div>
          <div>
            <label for="staff-address" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Address</label>
            <textarea
              id="staff-address"
              v-model="form.address"
              rows="2"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <DatePicker
                id="staff-joining"
                v-model="form.joining_date"
                label="Joining date"
                clearable
              />
            </div>
            <div>
              <SearchableSelect
                id="staff-department"
                v-model="form.department_id"
                label="Department"
                :options="departments"
                label-key="name"
                value-key="id"
                placeholder="—"
                search-placeholder="Search departments…"
                clearable
              />
            </div>
          </div>
          <div>
            <SearchableSelect
              id="staff-designation"
              v-model="form.designation_id"
              label="Designation"
              :options="designationOptions"
              label-key="label"
              value-key="id"
              placeholder="—"
              search-placeholder="Search designations…"
              clearable
            />
          </div>
          <div>
            <label for="staff-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
            <select
              id="staff-status"
              v-model="form.status"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="archived">Archived</option>
            </select>
          </div>

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
import { getDepartments } from '../services/departmentService.js';
import { getDesignations } from '../services/designationService.js';
import { createStaff, updateStaff } from '../services/staffService.js';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  staff: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.staff);

const form = reactive({
  first_name: '',
  last_name: '',
  gender: '',
  date_of_birth: '',
  phone: '',
  email: '',
  address: '',
  joining_date: '',
  department_id: '',
  designation_id: '',
  employee_type: 'staff',
  status: 'active',
});

const saving = ref(false);
const formError = ref(null);
const departments = ref([]);
const designations = ref([]);

const designationOptions = computed(() =>
  designations.value.map((d) => ({
    ...d,
    label: d.department_name ? `${d.name} (${d.department_name})` : d.name,
  }))
);

async function loadOptions() {
  try {
    [departments.value, designations.value] = await Promise.all([getDepartments(), getDesignations()]);
  } catch {
    departments.value = [];
    designations.value = [];
  }
}

function resetForm() {
  form.first_name = '';
  form.last_name = '';
  form.gender = '';
  form.date_of_birth = '';
  form.phone = '';
  form.email = '';
  form.address = '';
  form.joining_date = '';
  form.department_id = '';
  form.designation_id = '';
  form.employee_type = 'staff';
  form.status = 'active';
  formError.value = null;
}

function assign(s) {
  if (!s) {
    resetForm();
    return;
  }
  form.first_name = s.first_name ?? '';
  form.last_name = s.last_name ?? '';
  form.gender = s.gender ?? '';
  form.date_of_birth = s.date_of_birth ?? '';
  form.phone = s.phone ?? '';
  form.email = s.email ?? '';
  form.address = s.address ?? '';
  form.joining_date = s.joining_date ?? '';
  form.department_id = s.department_id ?? '';
  form.designation_id = s.designation_id ?? '';
  form.employee_type = s.employee_type ?? 'staff';
  form.status = s.status ?? 'active';
  formError.value = null;
}

watch(
  () => [props.modelValue, props.staff],
  async () => {
    if (props.modelValue) {
      await loadOptions();
      assign(props.staff);
    }
  },
  { immediate: true }
);

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  const payload = {
    first_name: form.first_name,
    last_name: form.last_name || null,
    gender: form.gender || null,
    date_of_birth: form.date_of_birth || null,
    phone: form.phone || null,
    email: form.email || null,
    address: form.address || null,
    joining_date: form.joining_date || null,
    department_id: form.department_id ? Number(form.department_id) : null,
    designation_id: form.designation_id ? Number(form.designation_id) : null,
    employee_type: form.employee_type,
    status: form.status,
  };
  try {
    if (isEdit.value) {
      await updateStaff(props.staff.id, payload);
    } else {
      await createStaff(payload);
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
