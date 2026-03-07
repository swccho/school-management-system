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
          <div>
            <label for="default_language" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Default language</label>
            <input
              id="default_language"
              v-model="form.default_language"
              type="text"
              placeholder="e.g. en"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="timezone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Timezone</label>
            <input
              id="timezone"
              v-model="form.timezone"
              type="text"
              placeholder="e.g. UTC"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="date_format" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Date format</label>
            <input
              id="date_format"
              v-model="form.date_format"
              type="text"
              placeholder="e.g. Y-m-d"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="time_format" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Time format</label>
            <input
              id="time_format"
              v-model="form.time_format"
              type="text"
              placeholder="e.g. H:i"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="default_currency" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Default currency</label>
            <input
              id="default_currency"
              v-model="form.default_currency"
              type="text"
              placeholder="e.g. USD"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
        </div>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
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

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
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
import { getSchoolSettings, updateSchoolSettings } from '../services/schoolService.js';

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
  } catch (err) {
    const msg = err.response?.data?.message;
    const errors = err.response?.data?.errors;
    error.value = msg || (errors ? Object.values(errors).flat().join(' ') : 'Failed to save settings.');
  } finally {
    saving.value = false;
  }
}

onMounted(loadSettings);
</script>
