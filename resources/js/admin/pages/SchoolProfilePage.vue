<template>
  <PageContainer
    title="School Profile"
    description="Manage your school's basic identity and contact information."
  >
    <div v-if="loading" class="rounded-xl border border-zinc-200 bg-white p-8 text-center dark:border-zinc-800 dark:bg-zinc-900">
      <p class="text-sm text-zinc-500 dark:text-zinc-400">Loading profile…</p>
    </div>
    <form v-else class="space-y-6" @submit.prevent="handleSubmit">
      <p
        v-if="error"
        class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
      >
        {{ error }}
      </p>
      <p
        v-if="success"
        class="rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400"
      >
        {{ success }}
      </p>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Basic Information</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">School name</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="short_name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Short name</label>
            <input
              id="short_name"
              v-model="form.short_name"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Code</label>
            <input
              id="code"
              v-model="form.code"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div class="sm:col-span-2">
            <label for="slogan" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Slogan</label>
            <input
              id="slogan"
              v-model="form.slogan"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div class="sm:col-span-2">
            <label for="principal_name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Principal name</label>
            <input
              id="principal_name"
              v-model="form.principal_name"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="established_year" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Established year</label>
            <input
              id="established_year"
              v-model.number="form.established_year"
              type="number"
              min="1900"
              max="2100"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div class="sm:col-span-2">
            <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
        </div>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Contact Information</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="phone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Phone</label>
            <input
              id="phone"
              v-model="form.phone"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div class="sm:col-span-2">
            <label for="website" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Website</label>
            <input
              id="website"
              v-model="form.website"
              type="url"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
        </div>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Address Information</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label for="address" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Address</label>
            <input
              id="address"
              v-model="form.address"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="city" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">City</label>
            <input
              id="city"
              v-model="form.city"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="district" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">District</label>
            <input
              id="district"
              v-model="form.district"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="country" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Country</label>
            <input
              id="country"
              v-model="form.country"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="postal_code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Postal code</label>
            <input
              id="postal_code"
              v-model="form.postal_code"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
        </div>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Branding</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label for="logo" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Logo</label>
            <div v-if="logoDisplayUrl" class="mt-1 flex items-center gap-3">
              <img
                :src="logoDisplayUrl"
                alt="School logo"
                class="h-16 max-w-[200px] rounded border border-zinc-200 object-contain dark:border-zinc-700"
                @error="logoLoadError = true"
              />
              <span v-if="logoLoadError" class="text-sm text-zinc-500 dark:text-zinc-400">Unable to load image</span>
            </div>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
              Choose a new file to replace the current logo.
            </p>
            <input
              id="logo"
              type="file"
              accept="image/*"
              class="mt-1 block w-full text-sm text-zinc-600 dark:text-zinc-400"
              @change="onLogoChange"
            />
          </div>
          <div>
            <label for="favicon" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Favicon</label>
            <div v-if="faviconDisplayUrl" class="mt-1 flex items-center gap-3">
              <img
                :src="faviconDisplayUrl"
                alt="Favicon"
                class="h-10 w-10 rounded border border-zinc-200 object-contain dark:border-zinc-700"
                @error="faviconLoadError = true"
              />
              <span v-if="faviconLoadError" class="text-sm text-zinc-500 dark:text-zinc-400">Unable to load image</span>
            </div>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
              Choose a new file to replace the current favicon.
            </p>
            <input
              id="favicon"
              type="file"
              accept="image/*"
              class="mt-1 block w-full text-sm text-zinc-600 dark:text-zinc-400"
              @change="onFaviconChange"
            />
          </div>
        </div>
      </section>

      <div class="flex justify-end">
        <button
          type="submit"
          class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-50 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
          :disabled="saving"
        >
          <span v-if="saving">Saving…</span>
          <span v-else>Save profile</span>
        </button>
      </div>
    </form>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getSchoolProfile, updateSchoolProfile } from '../services/schoolService.js';

const toast = useToast();

const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const success = ref(null);
const logoFile = ref(null);
const faviconFile = ref(null);

const form = reactive({
  name: '',
  code: '',
  email: '',
  phone: '',
  website: '',
  established_year: null,
  principal_name: '',
  slogan: '',
  short_name: '',
  address: '',
  city: '',
  district: '',
  country: '',
  postal_code: '',
  description: '',
  status: 'active',
  logo_path: '',
  favicon_path: '',
  logo_url: '',
  favicon_url: '',
});

const logoLoadError = ref(false);
const faviconLoadError = ref(false);

const logoDisplayUrl = computed(() => {
  if (logoFile.value) return URL.createObjectURL(logoFile.value);
  return form.logo_url || (form.logo_path ? `/storage/${form.logo_path}` : '');
});
const faviconDisplayUrl = computed(() => {
  if (faviconFile.value) return URL.createObjectURL(faviconFile.value);
  return form.favicon_url || (form.favicon_path ? `/storage/${form.favicon_path}` : '');
});

function assignProfile(data) {
  form.name = data.name ?? '';
  form.code = data.code ?? '';
  form.email = data.email ?? '';
  form.phone = data.phone ?? '';
  form.website = data.website ?? '';
  form.established_year = data.established_year ?? null;
  form.principal_name = data.principal_name ?? '';
  form.slogan = data.slogan ?? '';
  form.short_name = data.short_name ?? '';
  form.address = data.address ?? '';
  form.city = data.city ?? '';
  form.district = data.district ?? '';
  form.country = data.country ?? '';
  form.postal_code = data.postal_code ?? '';
  form.description = data.description ?? '';
  form.status = data.status ?? 'active';
  form.logo_path = data.logo_path ?? '';
  form.favicon_path = data.favicon_path ?? '';
  form.logo_url = data.logo_url ?? (data.logo_path ? `/storage/${data.logo_path}` : '');
  form.favicon_url = data.favicon_url ?? (data.favicon_path ? `/storage/${data.favicon_path}` : '');
  logoLoadError.value = false;
  faviconLoadError.value = false;
}

function onLogoChange(e) {
  logoFile.value = e.target.files?.[0] ?? null;
}

function onFaviconChange(e) {
  faviconFile.value = e.target.files?.[0] ?? null;
}

async function loadProfile() {
  loading.value = true;
  error.value = null;
  try {
    const data = await getSchoolProfile();
    assignProfile(data);
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Failed to load profile.';
  } finally {
    loading.value = false;
  }
}

async function handleSubmit() {
  saving.value = true;
  error.value = null;
  success.value = null;
  try {
    let data;
    if (logoFile.value || faviconFile.value) {
      const fd = new FormData();
      Object.entries(form).forEach(([k, v]) => {
        if (v != null && v !== '' && !['logo_path', 'favicon_path', 'logo_url', 'favicon_url'].includes(k)) fd.append(k, v);
      });
      if (logoFile.value) fd.append('logo', logoFile.value);
      if (faviconFile.value) fd.append('favicon', faviconFile.value);
      data = await updateSchoolProfile(fd);
    } else {
      data = await updateSchoolProfile(form);
    }
    success.value = 'Profile saved successfully.';
    toast.success('Profile saved successfully.');
    if (data?.school) {
      form.logo_path = data.school.logo_path ?? '';
      form.favicon_path = data.school.favicon_path ?? '';
      form.logo_url = data.school.logo_url ?? (form.logo_path ? `/storage/${form.logo_path}` : '');
      form.favicon_url = data.school.favicon_url ?? (form.favicon_path ? `/storage/${form.favicon_path}` : '');
      logoLoadError.value = false;
      faviconLoadError.value = false;
    }
    logoFile.value = null;
    faviconFile.value = null;
    const logoEl = document.getElementById('logo');
    const faviconEl = document.getElementById('favicon');
    if (logoEl) logoEl.value = '';
    if (faviconEl) faviconEl.value = '';
  } catch (err) {
    const msg = err.response?.data?.message;
    const errors = err.response?.data?.errors;
    const errMsg = msg || (errors ? Object.values(errors).flat().join(' ') : 'Failed to save profile.');
    error.value = errMsg;
    toast.error(errMsg);
  } finally {
    saving.value = false;
  }
}

onMounted(loadProfile);
</script>
