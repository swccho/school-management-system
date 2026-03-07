<template>
  <PageContainer
    title="System Settings"
    description="Configure localization, preferences, and system options."
  >
    <div v-if="loading" class="rounded-xl border border-zinc-200 bg-white p-8 text-center dark:border-zinc-800 dark:bg-zinc-900">
      <p class="text-sm text-zinc-500 dark:text-zinc-400">Loading settings…</p>
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
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Localization</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <SearchableSelect
            id="default_language"
            v-model="form.default_language"
            label="Default language"
            :options="languageOptions"
            label-key="label"
            value-key="value"
            placeholder="Select language"
            search-placeholder="Search language…"
            clearable
          />
          <SearchableSelect
            id="timezone"
            v-model="form.timezone"
            label="Timezone"
            :options="timezoneOptions"
            label-key="label"
            value-key="value"
            placeholder="Select timezone"
            search-placeholder="Search timezone…"
            clearable
          />
          <SearchableSelect
            id="date_format"
            v-model="form.date_format"
            label="Date format"
            :options="dateFormatOptions"
            label-key="label"
            value-key="value"
            placeholder="Select date format"
            search-placeholder="Search…"
            clearable
          />
          <SearchableSelect
            id="time_format"
            v-model="form.time_format"
            label="Time format"
            :options="timeFormatOptions"
            label-key="label"
            value-key="value"
            placeholder="Select time format"
            search-placeholder="Search…"
            clearable
          />
          <SearchableSelect
            id="default_currency"
            v-model="form.default_currency"
            label="Default currency"
            :options="currencyOptions"
            label-key="label"
            value-key="value"
            placeholder="Select currency"
            search-placeholder="Search currency…"
            clearable
          />
        </div>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900" style="display: none">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Attendance &amp; Result Preferences</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label for="attendance_mode" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Attendance mode</label>
            <input
              id="attendance_mode"
              v-model="form.attendance_mode"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="result_publish_policy" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Result publish policy</label>
            <input
              id="result_publish_policy"
              v-model="form.result_publish_policy"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
        </div>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900" style="display: none">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Maintenance &amp; Theme</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label for="theme" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Theme</label>
            <input
              id="theme"
              v-model="form.theme"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div class="flex items-center gap-3 pt-8">
            <input
              id="maintenance_mode"
              v-model="form.maintenance_mode"
              type="checkbox"
              class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800"
            />
            <label for="maintenance_mode" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Maintenance mode</label>
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
          <span v-else>Save settings</span>
        </button>
      </div>
    </form>
  </PageContainer>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getSchoolSettings, updateSchoolSettings } from '../services/schoolService.js';

const toast = useToast();

const languageOptions = [
  { value: 'en', label: 'English' },
  { value: 'bn', label: 'Bangla' },
];

const timezoneOptions = [
  { value: 'UTC', label: 'UTC' },
  { value: 'Asia/Dhaka', label: 'Asia/Dhaka' },
  { value: 'Asia/Kolkata', label: 'Asia/Kolkata' },
  { value: 'America/New_York', label: 'America/New_York' },
  { value: 'America/Los_Angeles', label: 'America/Los_Angeles' },
  { value: 'Europe/London', label: 'Europe/London' },
  { value: 'Europe/Paris', label: 'Europe/Paris' },
  { value: 'Asia/Dubai', label: 'Asia/Dubai' },
  { value: 'Asia/Singapore', label: 'Asia/Singapore' },
  { value: 'Asia/Tokyo', label: 'Asia/Tokyo' },
  { value: 'Australia/Sydney', label: 'Australia/Sydney' },
];

const dateFormatOptions = [
  { value: 'Y-m-d', label: 'Y-m-d (e.g. 2025-03-07)' },
  { value: 'd/m/Y', label: 'd/m/Y (e.g. 07/03/2025)' },
  { value: 'm/d/Y', label: 'm/d/Y (e.g. 03/07/2025)' },
  { value: 'd-m-Y', label: 'd-m-Y (e.g. 07-03-2025)' },
  { value: 'F j, Y', label: 'F j, Y (e.g. March 7, 2025)' },
  { value: 'j F Y', label: 'j F Y (e.g. 7 March 2025)' },
];

const timeFormatOptions = [
  { value: 'H:i', label: 'H:i (24h, e.g. 14:30)' },
  { value: 'h:i A', label: 'h:i A (12h, e.g. 2:30 PM)' },
  { value: 'H:i:s', label: 'H:i:s (24h with seconds)' },
  { value: 'h:i:s A', label: 'h:i:s A (12h with seconds)' },
];

const currencyOptions = [
  { value: 'BDT', label: 'BDT' },
  { value: 'USD', label: 'USD' },
];

const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const success = ref(null);

const form = reactive({
  default_language: '',
  timezone: '',
  date_format: '',
  time_format: '',
  default_currency: '',
  attendance_mode: '',
  result_publish_policy: '',
  theme: '',
  maintenance_mode: false,
});

function assignSettings(data) {
  form.default_language = data.default_language ?? '';
  form.timezone = data.timezone ?? '';
  form.date_format = data.date_format ?? '';
  form.time_format = data.time_format ?? '';
  form.default_currency = data.default_currency ?? '';
  form.attendance_mode = data.attendance_mode ?? '';
  form.result_publish_policy = data.result_publish_policy ?? '';
  form.theme = data.theme ?? '';
  form.maintenance_mode = data.maintenance_mode ?? false;
}

async function loadSettings() {
  loading.value = true;
  error.value = null;
  try {
    const data = await getSchoolSettings();
    assignSettings(data);
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Failed to load settings.';
  } finally {
    loading.value = false;
  }
}

async function handleSubmit() {
  saving.value = true;
  error.value = null;
  success.value = null;
  try {
    await updateSchoolSettings(form);
    success.value = 'Settings saved successfully.';
    toast.success('Settings saved successfully.');
  } catch (err) {
    const msg = err.response?.data?.message;
    const errors = err.response?.data?.errors;
    const errMsg = msg || (errors ? Object.values(errors).flat().join(' ') : 'Failed to save settings.');
    error.value = errMsg;
    toast.error(errMsg);
  } finally {
    saving.value = false;
  }
}

onMounted(loadSettings);
</script>
