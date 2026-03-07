<template>
  <form class="space-y-4" @submit.prevent="submit">
    <div>
      <label for="gs-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</label>
      <input
        id="gs-name"
        v-model="form.name"
        type="text"
        class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        placeholder="e.g. Default Scale"
      />
    </div>
    <div class="flex items-center gap-2">
      <input
        id="gs-default"
        v-model="form.is_default"
        type="checkbox"
        class="rounded border-zinc-300 dark:border-zinc-600"
      />
      <label for="gs-default" class="text-sm text-zinc-700 dark:text-zinc-300">Set as default scale</label>
    </div>
    <div>
      <label for="gs-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
      <select
        id="gs-status"
        v-model="form.status"
        class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
      >
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>
    <GradeScaleItemsEditor v-model="form.items" />
    <div class="flex gap-2 pt-2">
      <button
        type="submit"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
      >
        {{ submitLabel }}
      </button>
      <button
        v-if="showCancel"
        type="button"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
        @click="$emit('cancel')"
      >
        Cancel
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, watch } from 'vue';
import GradeScaleItemsEditor from './GradeScaleItemsEditor.vue';

const props = defineProps({
  modelValue: { type: Object, default: () => ({ name: '', is_default: false, status: 'active', items: [] }) },
  submitLabel: { type: String, default: 'Save' },
  showCancel: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'submit', 'cancel']);

const form = ref({
  name: props.modelValue?.name ?? '',
  is_default: props.modelValue?.is_default ?? false,
  status: props.modelValue?.status ?? 'active',
  items: props.modelValue?.items?.length ? [...props.modelValue.items] : [{ min_mark: 80, max_mark: 100, letter_grade: 'A+', grade_point: 5, remarks: '' }],
});

watch(form, () => emit('update:modelValue', { ...form.value }), { deep: true });
watch(() => props.modelValue, (v) => {
  if (v?.name !== undefined) form.value.name = v.name;
  if (v?.is_default !== undefined) form.value.is_default = v.is_default;
  if (v?.status !== undefined) form.value.status = v.status;
  if (v?.items?.length) form.value.items = [...v.items];
}, { deep: true });

function submit() {
  emit('submit', {
    name: form.value.name,
    is_default: !!form.value.is_default,
    status: form.value.status,
    items: form.value.items.map((i) => ({
      min_mark: Number(i.min_mark),
      max_mark: Number(i.max_mark),
      letter_grade: String(i.letter_grade).trim(),
      grade_point: Number(i.grade_point),
      remarks: i.remarks ? String(i.remarks).trim() : null,
    })),
  });
}
</script>
