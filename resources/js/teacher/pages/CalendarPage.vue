<template>
  <PageContainer title="Calendar" description="View school events and important dates.">
    <div class="mb-4 flex flex-wrap items-end gap-3">
      <div class="flex gap-2">
        <button
          type="button"
          :class="viewMode === 'list' ? 'bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'border border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-800'"
          class="rounded-lg px-3 py-2 text-sm font-medium"
          @click="viewMode = 'list'"
        >
          List
        </button>
        <button
          type="button"
          :class="viewMode === 'calendar' ? 'bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'border border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-800'"
          class="rounded-lg px-3 py-2 text-sm font-medium"
          @click="viewMode = 'calendar'; setCalendarMonthRange(); load();"
        >
          Calendar
        </button>
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-category" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Category</label>
        <select
          id="filter-category"
          v-model="categoryId"
          class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
          <option value="">All</option>
          <option v-for="c in categoryOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <div v-if="viewMode === 'list'" class="flex flex-1 flex-wrap items-end gap-3">
        <div class="flex flex-col gap-1">
          <label for="date-from" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">From</label>
          <input id="date-from" v-model="dateFrom" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
        </div>
        <div class="flex flex-col gap-1">
          <label for="date-to" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">To</label>
          <input id="date-to" v-model="dateTo" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
        </div>
      </div>
      <template v-if="viewMode === 'calendar'">
        <div class="flex items-center gap-2">
          <button type="button" class="rounded-lg border border-zinc-300 p-2 text-sm dark:border-zinc-600" aria-label="Previous month" @click="prevMonth">‹</button>
          <span class="min-w-[140px] text-center text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ calendarMonthLabel }}</span>
          <button type="button" class="rounded-lg border border-zinc-300 p-2 text-sm dark:border-zinc-600" aria-label="Next month" @click="nextMonth">›</button>
        </div>
      </template>
      <button type="button" class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-100 dark:text-zinc-900" @click="load">Load events</button>
    </div>
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <template v-else>
      <!-- List view -->
      <div v-if="viewMode === 'list'">
        <div v-if="events.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
          No events in this range.
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="e in events"
            :key="e.id"
            class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"
          >
            <div class="flex items-start justify-between gap-2">
              <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ e.title }}</p>
              <router-link
                :to="{ name: 'teacher-event-detail', params: { id: e.id } }"
                class="shrink-0 text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
              >
                View
              </router-link>
            </div>
            <p class="mt-1 text-sm text-zinc-500">{{ formatDate(e.start_datetime) }} – {{ formatDate(e.end_datetime) }}</p>
            <p v-if="e.location" class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ e.location }}</p>
            <p v-if="e.summary" class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ e.summary }}</p>
          </div>
        </div>
      </div>
      <!-- Calendar view -->
      <div v-else class="overflow-x-auto">
        <div class="min-w-[280px] rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
          <div class="grid grid-cols-7 border-b border-zinc-200 text-center text-xs font-medium text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
            <span class="py-2">Sun</span><span class="py-2">Mon</span><span class="py-2">Tue</span><span class="py-2">Wed</span><span class="py-2">Thu</span><span class="py-2">Fri</span><span class="py-2">Sat</span>
          </div>
          <div class="grid grid-cols-7">
            <button
              v-for="cell in calendarGrid"
              :key="cell.key"
              type="button"
              :class="[
                'min-h-[80px] min-w-0 border-b border-r border-zinc-200 p-2 text-left text-sm dark:border-zinc-700 touch-manipulation',
                cell.isCurrentMonth ? 'bg-white text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100' : 'bg-zinc-50 text-zinc-400 dark:bg-zinc-800/50 dark:text-zinc-500',
                cell.isToday ? 'ring-1 ring-zinc-400 dark:ring-zinc-500' : ''
              ]"
              @click="cell.date ? (selectedDay = cell.dateKey) : null"
            >
              <span v-if="cell.day" class="font-medium">{{ cell.day }}</span>
              <div v-if="cell.date && eventsForDay(cell.date).length" class="mt-1 space-y-0.5">
                <template v-for="ev in eventsForDay(cell.date).slice(0, 2)" :key="ev.id">
                  <router-link
                    :to="{ name: 'teacher-event-detail', params: { id: ev.id } }"
                    class="block truncate rounded px-1 py-0.5 text-xs bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-700 dark:hover:bg-zinc-600"
                    @click.stop
                  >
                    {{ ev.title }}
                  </router-link>
                </template>
                <span v-if="eventsForDay(cell.date).length > 2" class="block text-xs text-zinc-500">+{{ eventsForDay(cell.date).length - 2 }} more</span>
              </div>
            </button>
          </div>
        </div>
        <div v-if="selectedDay" class="mt-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <div class="flex items-center justify-between">
            <h3 class="font-medium text-zinc-900 dark:text-zinc-100">Events on {{ formatDayLabel(selectedDay) }}</h3>
            <button type="button" class="text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300" @click="selectedDay = null">Close</button>
          </div>
          <ul class="mt-2 space-y-2">
            <li v-for="e in eventsForDay(selectedDay)" :key="e.id" class="flex items-center justify-between rounded-lg border border-zinc-200 py-2 px-3 dark:border-zinc-700">
              <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ e.title }}</span>
              <router-link :to="{ name: 'teacher-event-detail', params: { id: e.id } }" class="text-sm text-zinc-600 dark:text-zinc-400">View</router-link>
            </li>
          </ul>
        </div>
      </div>
    </template>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { getEvents, getEventCategoryOptions } from '../services/eventService.js';

const loading = ref(true);
const events = ref([]);
const categoryOptions = ref([]);
const categoryId = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const viewMode = ref('list');
const calendarMonth = ref(new Date());
const selectedDay = ref(null);

function formatDate(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  return d.toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' });
}

function formatDayLabel(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr + 'T12:00:00').toLocaleDateString(undefined, { dateStyle: 'medium' });
}

function setCalendarMonthRange() {
  const y = calendarMonth.value.getFullYear();
  const m = calendarMonth.value.getMonth();
  const start = new Date(y, m, 1);
  const end = new Date(y, m + 1, 0);
  dateFrom.value = start.toISOString().slice(0, 10);
  dateTo.value = end.toISOString().slice(0, 10);
}

function prevMonth() {
  calendarMonth.value = new Date(calendarMonth.value.getFullYear(), calendarMonth.value.getMonth() - 1);
  setCalendarMonthRange();
  load();
}

function nextMonth() {
  calendarMonth.value = new Date(calendarMonth.value.getFullYear(), calendarMonth.value.getMonth() + 1);
  setCalendarMonthRange();
  load();
}

const calendarMonthLabel = computed(() => {
  return calendarMonth.value.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
});

const calendarGrid = computed(() => {
  const y = calendarMonth.value.getFullYear();
  const m = calendarMonth.value.getMonth();
  const first = new Date(y, m, 1);
  const last = new Date(y, m + 1, 0);
  const startDow = first.getDay();
  const daysInMonth = last.getDate();
  const prevMonthDays = new Date(y, m, 0).getDate();
  const cells = [];
  const today = new Date();
  const todayKey = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
  for (let i = 0; i < startDow; i++) {
    const d = prevMonthDays - startDow + i + 1;
    const date = new Date(y, m - 1, d);
    const dateKey = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
    cells.push({ key: 'p' + i, day: d, date, dateKey, isCurrentMonth: false, isToday: dateKey === todayKey });
  }
  for (let d = 1; d <= daysInMonth; d++) {
    const date = new Date(y, m, d);
    const dateKey = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
    cells.push({ key: 'c' + d, day: d, date, dateKey, isCurrentMonth: true, isToday: dateKey === todayKey });
  }
  const remaining = 42 - cells.length;
  for (let i = 0; i < remaining; i++) {
    const date = new Date(y, m + 1, i + 1);
    const dateKey = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
    cells.push({ key: 'n' + i, day: i + 1, date, dateKey, isCurrentMonth: false, isToday: dateKey === todayKey });
  }
  return cells;
});

function eventsForDay(day) {
  const dateKey = typeof day === 'string' ? day : (day.getFullYear() + '-' + String(day.getMonth() + 1).padStart(2, '0') + '-' + String(day.getDate()).padStart(2, '0'));
  return events.value.filter((e) => {
    if (!e.start_datetime) return false;
    const d = new Date(e.start_datetime);
    const key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    return key === dateKey;
  });
}

async function loadCategories() {
  try {
    categoryOptions.value = await getEventCategoryOptions();
  } catch {
    categoryOptions.value = [];
  }
}

async function load() {
  if (viewMode.value === 'calendar') {
    setCalendarMonthRange();
  }
  loading.value = true;
  try {
    const params = {};
    if (categoryId.value) params.category_id = categoryId.value;
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;
    events.value = await getEvents(params);
  } catch {
    events.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  await loadCategories();
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth(), 1);
  const end = new Date(now.getFullYear(), now.getMonth() + 2, 0);
  dateFrom.value = start.toISOString().slice(0, 10);
  dateTo.value = end.toISOString().slice(0, 10);
  load();
});
</script>
