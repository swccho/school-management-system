<template>
  <div ref="rootRef" class="relative">
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
      :id="computedId"
      ref="triggerRef"
      role="combobox"
      :aria-expanded="isOpen"
      :aria-haspopup="'listbox'"
      :aria-disabled="disabled"
      :aria-busy="loading"
      aria-autocomplete="list"
      :aria-controls="listboxId"
      tabindex="0"
      class="mt-1 flex min-h-[38px] w-full cursor-pointer items-center justify-between gap-2 rounded-lg border bg-white px-3 py-2 text-left text-sm transition-colors dark:bg-zinc-800 dark:text-zinc-100"
      :class="[
        error
          ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
          : 'border-zinc-300 dark:border-zinc-600 focus:border-zinc-400 focus:ring-2 focus:ring-zinc-400/20 dark:focus:border-zinc-500 dark:focus:ring-zinc-500/20',
        (disabled || loading) && 'cursor-not-allowed opacity-60',
        isOpen && 'ring-2 ring-zinc-400/20 dark:ring-zinc-500/20',
      ]"
      @click="toggleOpen"
      @keydown="onTriggerKeydown"
    >
      <span
        v-if="loading"
        class="shrink-0 text-zinc-500 dark:text-zinc-400"
      >
        {{ loadingText }}
      </span>
      <span
        v-else-if="selectedOption"
        class="truncate text-zinc-900 dark:text-zinc-100"
      >
        {{ getOptionLabel(selectedOption) }}
      </span>
      <span
        v-else
        class="truncate text-zinc-500 dark:text-zinc-400"
      >
        {{ placeholder }}
      </span>
      <span class="flex shrink-0 items-center gap-1">
        <button
          v-if="clearable && selectedOption && !disabled && !loading"
          type="button"
          class="rounded p-0.5 text-zinc-500 hover:bg-zinc-200 hover:text-zinc-700 dark:hover:bg-zinc-600 dark:hover:text-zinc-300"
          aria-label="Clear selection"
          @click.stop="clear"
        >
          <span aria-hidden="true">&times;</span>
        </button>
        <span
          class="pointer-events-none text-zinc-400 dark:text-zinc-500"
          :class="isOpen ? 'rotate-180' : ''"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </span>
      </span>
    </div>

    <div
      v-if="isOpen"
      :id="listboxId"
      ref="listboxRef"
      role="listbox"
      class="sidenav-scroll absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
      :class="dropdownClass"
    >
      <div class="sticky top-0 z-10 border-b border-zinc-100 bg-white px-2 py-1.5 dark:border-zinc-700 dark:bg-zinc-800">
        <input
          ref="searchInputRef"
          type="text"
          :placeholder="searchPlaceholder"
          class="w-full rounded-md border border-zinc-200 bg-zinc-50 px-2.5 py-1.5 text-sm placeholder-zinc-500 focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400/30 dark:border-zinc-600 dark:bg-zinc-900 dark:placeholder-zinc-400 dark:focus:border-zinc-500"
          :value="searchQuery"
          autocomplete="off"
          @input="onSearchInput"
          @keydown="onSearchKeydown"
        />
      </div>
      <div v-if="filteredOptions.length === 0" class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
        {{ options.length === 0 ? noOptionsText : noResultsText }}
      </div>
      <ul v-else class="py-1">
        <li
          v-for="(option, index) in filteredOptions"
          :key="getOptionValue(option)"
          role="option"
          :aria-selected="isSelected(option)"
          :data-index="index"
          class="cursor-pointer px-3 py-2 text-sm transition-colors"
          :class="[
            optionClass,
            highlightedIndex === index
              ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100'
              : 'text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-700/50',
            isSelected(option) && 'font-medium',
          ]"
          @click="selectOption(option)"
          @mousemove="highlightedIndex = index"
        >
          <slot name="option" :option="option" :label="getOptionLabel(option)" :selected="isSelected(option)">
            {{ getOptionLabel(option) }}
          </slot>
        </li>
      </ul>
    </div>

    <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </p>
    <p v-else-if="helpText" class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
      {{ helpText }}
    </p>
  </div>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: [String, Number], default: null },
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Select…' },
  labelKey: { type: String, default: 'name' },
  valueKey: { type: String, default: 'id' },
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  loadingText: { type: String, default: 'Loading…' },
  clearable: { type: Boolean, default: false },
  searchPlaceholder: { type: String, default: 'Search…' },
  noResultsText: { type: String, default: 'No results found.' },
  noOptionsText: { type: String, default: 'No options.' },
  name: { type: String, default: '' },
  id: { type: String, default: '' },
  label: { type: String, default: '' },
  error: { type: String, default: '' },
  helpText: { type: String, default: '' },
  required: { type: Boolean, default: false },
  dropdownClass: { type: String, default: '' },
  optionClass: { type: String, default: '' },
});

const emit = defineEmits([
  'update:modelValue',
  'change',
  'search',
  'open',
  'close',
  'clear',
]);

const rootRef = ref(null);
const triggerRef = ref(null);
const listboxRef = ref(null);
const searchInputRef = ref(null);
const isOpen = ref(false);
const searchQuery = ref('');
const highlightedIndex = ref(0);

const listboxId = computed(() => props.id ? `${props.id}-listbox` : `searchable-select-listbox-${Math.random().toString(36).slice(2, 9)}`);
const computedId = computed(() => props.id || undefined);

function getOptionLabel(option) {
  if (option == null) return '';
  const key = props.labelKey;
  return typeof option === 'object' && key in option ? String(option[key]) : String(option);
}

function getOptionValue(option) {
  if (option == null) return undefined;
  const key = props.valueKey;
  return typeof option === 'object' && key in option ? option[key] : option;
}

const selectedOption = computed(() => {
  if (props.modelValue == null || props.modelValue === '') return null;
  return props.options.find((opt) => getOptionValue(opt) === props.modelValue) ?? null;
});

const filteredOptions = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return props.options;
  return props.options.filter((opt) => getOptionLabel(opt).toLowerCase().includes(q));
});

function isSelected(option) {
  return props.modelValue !== null && props.modelValue !== '' && getOptionValue(option) === props.modelValue;
}

function toggleOpen() {
  if (props.disabled || props.loading) return;
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchQuery.value = '';
    highlightedIndex.value = Math.max(
      0,
      filteredOptions.value.findIndex((opt) => isSelected(opt))
    );
    emit('open');
    nextTick(() => {
      searchInputRef.value?.focus();
    });
  } else {
    emit('close');
  }
}

function selectOption(option) {
  const value = getOptionValue(option);
  emit('update:modelValue', value);
  emit('change', value);
  isOpen.value = false;
  emit('close');
}

function clear() {
  emit('update:modelValue', null);
  emit('change', null);
  emit('clear');
  isOpen.value = false;
  emit('close');
}

function onSearchInput(e) {
  searchQuery.value = e.target.value;
  emit('search', searchQuery.value);
  highlightedIndex.value = 0;
}

function onTriggerKeydown(e) {
  if (!isOpen.value) {
    if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
      e.preventDefault();
      toggleOpen();
    }
    return;
  }
  if (e.key === 'Escape') {
    e.preventDefault();
    isOpen.value = false;
    emit('close');
    triggerRef.value?.focus();
    return;
  }
  if (e.key === 'ArrowDown') {
    e.preventDefault();
    highlightedIndex.value = Math.min(highlightedIndex.value + 1, filteredOptions.value.length - 1);
    return;
  }
  if (e.key === 'ArrowUp') {
    e.preventDefault();
    highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0);
    return;
  }
  if (e.key === 'Enter' && filteredOptions.value[highlightedIndex.value]) {
    e.preventDefault();
    selectOption(filteredOptions.value[highlightedIndex.value]);
  }
}

function onSearchKeydown(e) {
  if (e.key === 'Escape') {
    e.preventDefault();
    isOpen.value = false;
    emit('close');
    triggerRef.value?.focus();
    return;
  }
  if (e.key === 'ArrowDown') {
    e.preventDefault();
    highlightedIndex.value = Math.min(highlightedIndex.value + 1, filteredOptions.value.length - 1);
    listboxRef.value?.querySelector(`[data-index="${highlightedIndex.value}"]`)?.scrollIntoView({ block: 'nearest' });
    return;
  }
  if (e.key === 'ArrowUp') {
    e.preventDefault();
    highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0);
    listboxRef.value?.querySelector(`[data-index="${highlightedIndex.value}"]`)?.scrollIntoView({ block: 'nearest' });
    return;
  }
  if (e.key === 'Enter' && filteredOptions.value[highlightedIndex.value]) {
    e.preventDefault();
    selectOption(filteredOptions.value[highlightedIndex.value]);
  }
}

function handleClickOutside(event) {
  if (!rootRef.value?.contains(event.target)) {
    if (isOpen.value) {
      isOpen.value = false;
      emit('close');
    }
  }
}

watch(isOpen, (open) => {
  if (open) {
    document.addEventListener('click', handleClickOutside);
  } else {
    document.removeEventListener('click', handleClickOutside);
  }
});

watch(
  () => props.options,
  () => {
    highlightedIndex.value = Math.min(highlightedIndex.value, Math.max(0, filteredOptions.value.length - 1));
  }
);
</script>
