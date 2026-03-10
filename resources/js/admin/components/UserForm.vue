<template>
  <form class="space-y-6" @submit.prevent="handleSubmit">
    <p
      v-if="formError"
      class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
    >
      {{ formError }}
    </p>

    <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
      <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Basic information</h3>
      <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
          <label for="user-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name <span class="text-red-500">*</span></label>
          <input
            id="user-name"
            v-model="form.name"
            type="text"
            required
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
        <div>
          <label for="user-email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email <span class="text-red-500">*</span></label>
          <input
            id="user-email"
            v-model="form.email"
            type="email"
            required
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
        <div>
          <label for="user-username" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Username</label>
          <input
            id="user-username"
            v-model="form.username"
            type="text"
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
        <div>
          <label for="user-phone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Phone</label>
          <input
            id="user-phone"
            v-model="form.phone"
            type="text"
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
        <div v-if="!isEdit">
          <label for="user-password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Password <span class="text-red-500">*</span></label>
          <input
            id="user-password"
            v-model="form.password"
            type="password"
            :required="!isEdit"
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
        <div v-if="!isEdit">
          <label for="user-password-confirm" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Confirm password <span class="text-red-500">*</span></label>
          <input
            id="user-password-confirm"
            v-model="form.password_confirmation"
            type="password"
            :required="!isEdit"
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
        <div v-if="isEdit">
          <label for="user-password-new" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">New password (leave blank to keep)</label>
          <input
            id="user-password-new"
            v-model="form.password"
            type="password"
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
        <div v-if="isEdit && form.password">
          <label for="user-password-confirm-edit" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Confirm new password <span class="text-red-500">*</span></label>
          <input
            id="user-password-confirm-edit"
            v-model="form.password_confirmation"
            type="password"
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
        <div>
          <label for="user-type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">User type</label>
          <select
            id="user-type"
            v-model="form.user_type"
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          >
            <option value="">—</option>
            <option value="admin">Admin</option>
            <option value="teacher">Teacher</option>
            <option value="staff">Staff</option>
            <option value="student">Student</option>
            <option value="guardian">Guardian</option>
          </select>
        </div>
        <div>
          <label for="user-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status <span class="text-red-500">*</span></label>
          <select
            id="user-status"
            v-model="form.status"
            required
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          >
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Roles <span class="text-red-500">*</span></label>
          <div class="mt-2 flex flex-wrap gap-3">
            <label
              v-for="r in rolesOptions"
              :key="r.id"
              class="inline-flex items-center gap-2"
            >
              <input
                v-model="form.role_ids"
                type="checkbox"
                :value="r.id"
                class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800"
              />
              <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ r.name }}</span>
            </label>
          </div>
          <p v-if="rolesOptions.length === 0" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">No roles available.</p>
        </div>
        <div class="sm:col-span-2">
          <label for="link-type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Link to existing record</label>
          <div class="mt-2 flex flex-wrap gap-4">
            <select
              id="link-type"
              v-model="form.link_type"
              class="block rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="">None</option>
              <option value="staff">Staff</option>
              <option value="student">Student</option>
              <option value="guardian">Guardian</option>
            </select>
            <select
              v-if="form.link_type"
              v-model="form.link_id"
              :class="[
                'block rounded-lg border bg-white px-3 py-2 text-sm dark:bg-zinc-800 dark:text-zinc-100',
                form.link_type ? 'border-zinc-300 dark:border-zinc-600' : 'border-zinc-200 dark:border-zinc-700',
              ]"
            >
              <option :value="null">Select {{ form.link_type }}</option>
              <option
                v-for="item in linkableEntitiesList"
                :key="item.id"
                :value="item.id"
              >
                {{ item.label }}
              </option>
            </select>
          </div>
          <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Link this user to a staff, student, or guardian so they can sign in to the corresponding portal.</p>
        </div>
        <div class="sm:col-span-2">
          <label for="user-avatar" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Avatar</label>
          <input
            id="user-avatar"
            type="file"
            accept="image/*"
            class="mt-1 block w-full text-sm text-zinc-600 dark:text-zinc-400"
            @change="onAvatarChange"
          />
          <p v-if="user?.avatar_url" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Current avatar is set. Upload a new file to replace.</p>
        </div>
      </div>
    </section>

    <div class="flex justify-end gap-2">
      <router-link
        :to="{ name: 'users' }"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        Cancel
      </router-link>
      <button
        type="submit"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        :disabled="submitting"
      >
        {{ submitting ? 'Saving…' : (isEdit ? 'Update user' : 'Create user') }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { createUser, updateUser } from '../services/userService.js';

const props = defineProps({
  user: { type: Object, default: null },
  rolesOptions: { type: Array, default: () => [] },
  linkableOptions: {
    type: Object,
    default: () => ({ staffs: [], students: [], guardians: [] }),
  },
});

const emit = defineEmits(['saved']);

const isEdit = computed(() => !!props.user);

const linkableEntitiesList = computed(() => {
  const type = form.value.link_type;
  if (!type) return [];
  const key = type === 'staff' ? 'staffs' : type === 'student' ? 'students' : 'guardians';
  return props.linkableOptions[key] ?? [];
});

const form = ref({
  name: '',
  email: '',
  username: '',
  phone: '',
  password: '',
  password_confirmation: '',
  user_type: '',
  status: 'active',
  role_ids: [],
  link_type: '',
  link_id: null,
});
const avatarFile = ref(null);
const formError = ref(null);
const submitting = ref(false);

function resetForm() {
  form.value = {
    name: '',
    email: '',
    username: '',
    phone: '',
    password: '',
    password_confirmation: '',
    user_type: '',
    status: 'active',
    role_ids: [],
    link_type: '',
    link_id: null,
  };
  avatarFile.value = null;
  formError.value = null;
}

function getInitialLink(user) {
  if (user?.staff) {
    return { link_type: 'staff', link_id: user.staff.id };
  }
  if (user?.student) {
    return { link_type: 'student', link_id: user.student.id };
  }
  if (user?.student_guardians?.length) {
    return { link_type: 'guardian', link_id: user.student_guardians[0].id };
  }
  return { link_type: '', link_id: null };
}

watch(() => props.user, (u) => {
  if (u) {
    const link = getInitialLink(u);
    form.value = {
      name: u.name ?? '',
      email: u.email ?? '',
      username: u.username ?? '',
      phone: u.phone ?? '',
      password: '',
      password_confirmation: '',
      user_type: u.user_type ?? '',
      status: u.status ?? 'active',
      role_ids: (u.roles || []).map((r) => r.id),
      link_type: link.link_type,
      link_id: link.link_id,
    };
    avatarFile.value = null;
  } else {
    resetForm();
  }
}, { immediate: true });

watch(() => form.value.link_type, () => {
  form.value.link_id = null;
});

function onAvatarChange(e) {
  const file = e.target.files?.[0];
  avatarFile.value = file || null;
}

function buildPayload() {
  const f = form.value;
  const payload = {
    name: f.name,
    email: f.email,
    username: f.username || null,
    phone: f.phone || null,
    user_type: f.user_type || null,
    status: f.status,
    role_ids: Array.isArray(f.role_ids) ? f.role_ids : [],
  };
  if (!isEdit.value) {
    payload.password = f.password;
    payload.password_confirmation = f.password_confirmation;
  } else if (f.password) {
    payload.password = f.password;
    payload.password_confirmation = f.password_confirmation;
  }
  if (f.link_type) {
    payload.link_type = f.link_type;
    payload.link_id = f.link_id ?? null;
  } else {
    payload.link_type = null;
    payload.link_id = null;
  }
  return payload;
}

function buildFormData(payload) {
  const fd = new FormData();
  fd.append('name', payload.name);
  fd.append('email', payload.email);
  fd.append('username', payload.username || '');
  fd.append('phone', payload.phone || '');
  fd.append('user_type', payload.user_type || '');
  fd.append('status', payload.status);
  payload.role_ids.forEach((id) => fd.append('role_ids[]', id));
  if (payload.password) {
    fd.append('password', payload.password);
    fd.append('password_confirmation', payload.password_confirmation);
  }
  if (avatarFile.value) {
    fd.append('avatar', avatarFile.value);
  }
  if (payload.link_type) {
    fd.append('link_type', payload.link_type);
    fd.append('link_id', payload.link_id ?? '');
  } else {
    fd.append('link_type', '');
    fd.append('link_id', '');
  }
  return fd;
}

async function handleSubmit() {
  formError.value = null;
  if (!isEdit.value && (!form.value.password || form.value.password.length < 8)) {
    formError.value = 'Password must be at least 8 characters.';
    return;
  }
  if (form.value.password && form.value.password !== form.value.password_confirmation) {
    formError.value = 'Passwords do not match.';
    return;
  }
  if (!form.value.role_ids?.length) {
    formError.value = 'Select at least one role.';
    return;
  }

  submitting.value = true;
  try {
    const payload = buildPayload();
    if (avatarFile.value) {
      const fd = buildFormData(payload);
      if (isEdit.value) {
        await updateUser(props.user.id, fd);
      } else {
        await createUser(fd);
      }
    } else {
      if (isEdit.value) {
        await updateUser(props.user.id, payload);
      } else {
        await createUser(payload);
      }
    }
    emit('saved');
  } catch (err) {
    const msg = err.response?.data?.message;
    const errors = err.response?.data?.errors;
    if (errors) {
      const first = Object.values(errors).flat()[0];
      formError.value = first || msg || 'Validation failed.';
    } else {
      formError.value = msg || 'Failed to save user.';
    }
  } finally {
    submitting.value = false;
  }
}
</script>
