<template>
  <div class="relative">
    <label
      v-if="label"
      :for="computedId"
      class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <div class="relative mt-1 overflow-visible cursor-pointer" @click="openCalendar">
      <input
        :id="computedId"
        ref="inputRef"
        type="text"
        :placeholder="placeholder"
        :disabled="disabled"
        readonly
        autocomplete="off"
        class="block w-full rounded-lg border bg-white px-3 py-2 pr-8 text-sm transition-colors dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-100"
        :class="[
          error
            ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
            : 'border-zinc-300 dark:border-zinc-600 focus:border-zinc-400 focus:ring-2 focus:ring-zinc-400/20 dark:focus:border-zinc-500 dark:focus:ring-zinc-500/20',
          disabled && 'cursor-not-allowed opacity-60',
        ]"
        :aria-label="label || placeholder"
        :aria-invalid="!!error"
      />
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
        <button
          v-if="clearable && hasValue && !disabled"
          type="button"
          class="pointer-events-auto rounded p-0.5 text-zinc-500 hover:bg-zinc-200 hover:text-zinc-700 dark:hover:bg-zinc-600 dark:hover:text-zinc-300"
          aria-label="Clear range"
          @click.stop="clear"
        >
          <span aria-hidden="true">&times;</span>
        </button>
        <svg
          v-else
          class="h-4 w-4 text-zinc-400 dark:text-zinc-500"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
        </svg>
      </div>
    </div>
    <p v-if="error" :id="`${computedId}-error`" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import 'flatpickr/dist/themes/dark.css';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ date_from: '', date_to: '' }),
  },
  placeholder: { type: String, default: 'Select date range…' },
  label: { type: String, default: '' },
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  clearable: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
  id: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'change']);

const dateTimeSettings = inject('dateTimeSettings', ref({ date_format: 'Y-m-d', timezone: '' }));
const displayDateFormat = computed(() => dateTimeSettings.value?.date_format || 'Y-m-d');

const inputRef = ref(null);
let fp = null;

const computedId = computed(
  () => props.id || `daterangepicker-${Math.random().toString(36).slice(2, 9)}`
);

const hasValue = computed(
  () => !!(props.modelValue?.date_from?.trim() && props.modelValue?.date_to?.trim())
);

function toYmd(dateObj) {
  if (!dateObj) return '';
  const d = dateObj;
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

function onFlatpickrChange(selectedDates) {
  if (selectedDates.length >= 2) {
    const date_from = toYmd(selectedDates[0]);
    const date_to = toYmd(selectedDates[1]);
    emit('update:modelValue', { date_from, date_to });
    emit('change', { date_from, date_to });
  } else if (selectedDates.length === 0) {
    emit('update:modelValue', { date_from: '', date_to: '' });
    emit('change', { date_from: '', date_to: '' });
  }
}

function clear() {
  emit('update:modelValue', { date_from: '', date_to: '' });
  emit('change', { date_from: '', date_to: '' });
  if (fp) fp.clear();
}

function openCalendar() {
  if (props.disabled) return;
  const instance = fp ?? inputRef.value?._flatpickr;
  if (instance?.open) instance.open();
  else if (instance?.toggle) instance.toggle();
  else if (inputRef.value) {
    inputRef.value.focus();
    inputRef.value.dispatchEvent(new MouseEvent('click', { bubbles: true }));
  }
}

onMounted(() => {
  if (!inputRef.value) return;
  const config = {
    mode: 'range',
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: displayDateFormat.value,
    allowInput: false,
    theme: 'dark',
    static: true,
    clickOpens: true,
    onChange: onFlatpickrChange,
  };
  const v = props.modelValue;
  if (v?.date_from && v?.date_to) {
    config.defaultDate = [v.date_from, v.date_to];
  }
  fp = flatpickr(inputRef.value, config);
});

onBeforeUnmount(() => {
  if (fp) {
    fp.destroy();
    fp = null;
  }
});

watch(
  () => props.modelValue,
  (val) => {
    if (!fp) return;
    if (val?.date_from && val?.date_to) {
      fp.setDate([val.date_from, val.date_to], false);
    } else {
      fp.clear();
    }
  },
  { deep: true }
);

watch(
  () => props.disabled,
  (val) => {
    if (fp) {
      fp.input.disabled = val;
    }
  }
);

watch(displayDateFormat, (newFormat) => {
  if (fp && newFormat) {
    fp.set('altFormat', newFormat);
  }
});
</script>
