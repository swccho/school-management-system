<template>
  <div class="relative">
    <input
      v-if="name"
      type="hidden"
      :name="name"
      :value="modelValue ?? ''"
    />
    <label
      v-if="label"
      :for="computedId"
      class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <div
      class="relative mt-1 overflow-visible cursor-pointer"
      @click="openCalendar"
    >
      <input
        :id="computedId"
        ref="inputRef"
        type="text"
        :value="modelValue || ''"
        :placeholder="placeholder"
        :disabled="disabled"
        readonly
        autocomplete="off"
        class="block w-full rounded-lg border bg-white px-3 py-2 pr-8 text-sm transition-colors dark:bg-zinc-800 dark:text-zinc-100"
        :class="[
          error
            ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
            : 'border-zinc-300 dark:border-zinc-600 focus:border-zinc-400 focus:ring-2 focus:ring-zinc-400/20 dark:focus:border-zinc-500 dark:focus:ring-zinc-500/20',
          disabled && 'cursor-not-allowed opacity-60',
        ]"
        :aria-label="label || placeholder"
        :aria-invalid="!!error"
        :aria-describedby="error ? `${computedId}-error` : undefined"
      />
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
        <button
          v-if="clearable && modelValue && !disabled"
          type="button"
          class="pointer-events-auto rounded p-0.5 text-zinc-500 hover:bg-zinc-200 hover:text-zinc-700 dark:hover:bg-zinc-600 dark:hover:text-zinc-300"
          aria-label="Clear date"
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
    <p
      v-if="error"
      :id="`${computedId}-error`"
      class="mt-1 text-sm text-red-600 dark:text-red-400"
    >
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
  modelValue: { type: String, default: '' },
  mode: { type: String, default: 'date', validator: (v) => ['date', 'datetime'].includes(v) },
  placeholder: { type: String, default: 'Select date…' },
  label: { type: String, default: '' },
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  clearable: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
  id: { type: String, default: '' },
  name: { type: String, default: '' },
  dateFormat: { type: String, default: '' },
  timeFormat: { type: String, default: '' },
  timezone: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'change']);

const dateTimeSettings = inject('dateTimeSettings', ref({ date_format: 'Y-m-d', time_format: 'H:i', timezone: '' }));

const displayDateFormat = computed(() => {
  if (props.dateFormat) return props.dateFormat;
  return props.mode === 'datetime'
    ? `${dateTimeSettings.value?.date_format || 'Y-m-d'} ${dateTimeSettings.value?.time_format || 'H:i'}`
    : (dateTimeSettings.value?.date_format || 'Y-m-d');
});

const inputRef = ref(null);
let fp = null;

const computedId = computed(
  () => props.id || `datepicker-${Math.random().toString(36).slice(2, 9)}`
);

function formatDateForBackend(dateObj) {
  if (!dateObj) return '';
  const d = dateObj;
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  if (props.mode === 'datetime') {
    const h = String(d.getHours()).padStart(2, '0');
    const min = String(d.getMinutes()).padStart(2, '0');
    const sec = String(d.getSeconds()).padStart(2, '0');
    return `${y}-${m}-${day} ${h}:${min}:${sec}`;
  }
  return `${y}-${m}-${day}`;
}

function onFlatpickrChange(selectedDates) {
  const val = selectedDates.length ? formatDateForBackend(selectedDates[0]) : '';
  emit('update:modelValue', val);
  emit('change', val);
}

function clear() {
  emit('update:modelValue', '');
  emit('change', '');
  if (fp) fp.clear();
}

function openCalendar() {
  if (props.disabled) return;
  const instance = fp ?? inputRef.value?._flatpickr;
  if (instance?.open) {
    instance.open();
  } else if (instance?.toggle) {
    instance.toggle();
  } else if (inputRef.value) {
    inputRef.value.focus();
    inputRef.value.dispatchEvent(new MouseEvent('click', { bubbles: true }));
  }
}

onMounted(() => {
  if (!inputRef.value) return;
  const config = {
    dateFormat: props.mode === 'datetime' ? 'Y-m-d H:i:s' : 'Y-m-d',
    altInput: true,
    altFormat: displayDateFormat.value,
    enableTime: props.mode === 'datetime',
    allowInput: false,
    theme: 'dark',
    static: true,
    clickOpens: true,
    onChange: onFlatpickrChange,
  };
  if (props.modelValue) {
    config.defaultDate = props.modelValue;
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
    if (fp) {
      if (val) {
        fp.setDate(val, false);
      } else {
        fp.clear();
      }
    }
  }
);

watch(
  () => props.disabled,
  (val) => {
    if (fp) {
      fp.set('allowInput', !val);
      fp.input.disabled = val;
    }
  }
);

watch(
  displayDateFormat,
  (newFormat) => {
    if (fp && newFormat) {
      fp.set('altFormat', newFormat);
    }
  }
);
</script>
