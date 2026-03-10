<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="emit('close')"
    >
      <div class="absolute inset-0 bg-zinc-900/50" aria-hidden="true" />
      <div
        class="sidenav-scroll relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="isEdit ? 'edit-banner-title' : 'add-banner-title'"
      >
        <h2
          :id="isEdit ? 'edit-banner-title' : 'add-banner-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit banner' : 'Add banner' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div>
            <label for="banner-title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title <span class="text-red-500">*</span></label>
            <input
              id="banner-title"
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>

          <div>
            <label for="banner-subtitle" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Subtitle</label>
            <input
              id="banner-subtitle"
              v-model="form.subtitle"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="Optional"
            />
          </div>

          <div>
            <label for="banner-image" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
              Image <span v-if="!isEdit" class="text-red-500">*</span>
            </label>
            <input
              id="banner-image"
              type="file"
              accept="image/jpeg,image/png,image/gif,image/webp"
              class="mt-1 block w-full text-sm text-zinc-600 file:mr-2 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-zinc-700 dark:text-zinc-400 dark:file:bg-zinc-700 dark:file:text-zinc-200"
              @change="onImageChange"
            />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">JPEG, PNG, GIF, WebP. Max 5MB. Leave empty on edit to keep current.</p>
            <p v-if="isEdit && existingImageUrl" class="mt-1 text-xs">
              <a :href="existingImageUrl" target="_blank" rel="noopener noreferrer" class="underline">View current</a>
            </p>
          </div>

          <div>
            <label for="banner-button-text" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Button text</label>
            <input
              id="banner-button-text"
              v-model="form.button_text"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="Optional"
            />
          </div>

          <div>
            <label for="banner-button-url" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Button URL</label>
            <input
              id="banner-button-url"
              v-model="form.button_url"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="Optional"
            />
          </div>

          <div>
            <label for="banner-sort-order" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Sort order</label>
            <input
              id="banner-sort-order"
              v-model.number="form.sort_order"
              type="number"
              min="0"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>

          <div>
            <label for="banner-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status <span class="text-red-500">*</span></label>
            <select
              id="banner-status"
              v-model="form.status"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
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
import { useToast } from '../../shared/composables/useToast.js';
import { create, update, getOne } from '../services/bannerService.js';

const toast = useToast();

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  banner: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.banner?.id);

const form = reactive({
  title: '',
  subtitle: '',
  button_text: '',
  button_url: '',
  sort_order: 0,
  status: 'active',
});

const saving = ref(false);
const formError = ref(null);
const imageFile = ref(null);
const existingImageUrl = ref(null);

function resetForm() {
  form.title = '';
  form.subtitle = '';
  form.button_text = '';
  form.button_url = '';
  form.sort_order = 0;
  form.status = 'active';
  formError.value = null;
  imageFile.value = null;
  existingImageUrl.value = null;
}

function assignBanner(b) {
  if (!b) {
    resetForm();
    return;
  }
  form.title = b.title ?? '';
  form.subtitle = b.subtitle ?? '';
  form.button_text = b.button_text ?? '';
  form.button_url = b.button_url ?? '';
  form.sort_order = b.sort_order ?? 0;
  form.status = b.status ?? 'active';
  formError.value = null;
  imageFile.value = null;
  existingImageUrl.value = b.image_url ?? null;
}

function onImageChange(e) {
  const file = e.target.files?.[0];
  imageFile.value = file ?? null;
}

watch(
  () => [props.modelValue, props.banner],
  async () => {
    if (props.modelValue) {
      if (props.banner?.id) {
        try {
          const full = await getOne(props.banner.id);
          assignBanner(full);
        } catch {
          assignBanner(props.banner);
        }
      } else {
        assignBanner(null);
      }
    }
  },
  { immediate: true }
);

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  const payload = {
    title: form.title.trim(),
    subtitle: form.subtitle?.trim() || null,
    button_text: form.button_text?.trim() || null,
    button_url: form.button_url?.trim() || null,
    sort_order: form.sort_order,
    status: form.status,
  };
  if (imageFile.value) payload.image = imageFile.value;

  try {
    if (isEdit.value) {
      await update(props.banner.id, payload);
    } else {
      if (!payload.image) {
        formError.value = 'Image is required for new banners.';
        toast.error(formError.value);
        saving.value = false;
        return;
      }
      await create(payload);
    }
    emit('saved');
    emit('update:modelValue', false);
    emit('close');
  } catch (err) {
    const msg = err.response?.data?.message;
    const errors = err.response?.data?.errors;
    formError.value = msg || (errors ? Object.values(errors).flat().join(' ') : 'Something went wrong.');
    toast.error(formError.value);
  } finally {
    saving.value = false;
  }
}
</script>
