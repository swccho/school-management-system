<template>
  <PageContainer
    title="Help"
    description="Browse documentation and search for help."
  >
    <!-- Search bar at top -->
    <div class="mb-6">
      <label for="help-search" class="sr-only">Search help</label>
      <input
        id="help-search"
        v-model="searchQuery"
        type="search"
        placeholder="Search guides by title, description, content, or category…"
        class="block w-full max-w-xl rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-zinc-400 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
        autocomplete="off"
        @input="onSearchInput"
      />
    </div>

    <div class="flex flex-col gap-6 lg:flex-row">
      <!-- Sticky sidebar -->
      <aside class="w-full shrink-0 lg:sticky lg:top-20 lg:self-start lg:max-h-[calc(100vh-6rem)] lg:overflow-y-auto lg:w-64">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
            {{ searchQuery.trim() ? 'Search results' : 'Categories' }}
          </p>

          <!-- Search results -->
          <div v-if="searchQuery.trim()">
            <div v-if="searching" class="py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
              Searching…
            </div>
            <div v-else-if="searchResults.length > 0" class="space-y-0.5">
              <button
                v-for="r in searchResults"
                :key="r.id"
                type="button"
                class="block w-full rounded-lg px-3 py-2.5 text-left text-sm transition-colors"
                :class="currentSlug === r.slug
                  ? 'bg-zinc-200 font-medium text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100'
                  : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800'"
                @click="openGuideBySlug(r.slug)"
              >
                <span class="block truncate">{{ r.title }}</span>
                <span v-if="r.category" class="mt-0.5 block truncate text-xs text-zinc-500 dark:text-zinc-400">{{ r.category.name }}</span>
              </button>
            </div>
            <div v-else class="rounded-lg border border-zinc-200 bg-zinc-50 py-8 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
              <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">No results found</p>
              <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">Try different keywords or browse categories below.</p>
              <button
                type="button"
                class="mt-3 text-sm font-medium text-zinc-700 underline hover:no-underline dark:text-zinc-300"
                @click="searchQuery = ''"
              >
                Clear search
              </button>
            </div>
          </div>

          <!-- Categories -->
          <div v-else class="space-y-3">
            <div v-for="cat in helpCategories" :key="cat.id" class="space-y-1">
              <p class="px-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ cat.name }}</p>
              <div class="space-y-0.5">
                <button
                  v-for="g in guidesByCategory[cat.id]"
                  :key="g.id"
                  type="button"
                  class="block w-full rounded-lg px-3 py-2 text-left text-sm transition-colors"
                  :class="currentSlug === g.slug
                    ? 'bg-zinc-200 font-medium text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100'
                    : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800'"
                  @click="openGuideBySlug(g.slug)"
                >
                  {{ g.title }}
                </button>
              </div>
            </div>
            <p v-if="helpCategories.length === 0 && !loadingCategories" class="px-2 py-4 text-sm text-zinc-500 dark:text-zinc-400">
              No categories yet. An administrator can add guide categories and publish guides.
            </p>
          </div>
        </div>
      </aside>

      <!-- Main content -->
      <main class="min-w-0 flex-1">
        <div
          v-if="loadingGuide"
          class="flex items-center justify-center rounded-xl border border-zinc-200 bg-white py-16 dark:border-zinc-800 dark:bg-zinc-900"
        >
          <p class="text-sm text-zinc-500 dark:text-zinc-400">Loading…</p>
        </div>

        <div
          v-else-if="guideError"
          class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-900/30 dark:bg-red-900/20"
        >
          <p class="text-sm font-medium text-red-700 dark:text-red-400">{{ guideError }}</p>
          <router-link
            :to="{ name: 'help', query: {} }"
            class="mt-3 inline-block text-sm font-medium text-red-600 underline hover:no-underline dark:text-red-300"
          >
            Back to Help
          </router-link>
        </div>

        <div
          v-else-if="currentGuide"
          class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900 md:p-8"
        >
          <p v-if="currentGuide.category" class="mb-1 text-sm font-medium text-zinc-500 dark:text-zinc-400">
            {{ currentGuide.category.name }}
          </p>
          <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 md:text-3xl">
            {{ currentGuide.title }}
          </h1>
          <p v-if="currentGuide.short_description" class="mt-2 text-zinc-600 dark:text-zinc-400">
            {{ currentGuide.short_description }}
          </p>
          <p v-if="currentGuide.updated_at" class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">
            Updated {{ formatDate(currentGuide.updated_at) }}
          </p>
          <div
            class="guide-content prose prose-zinc mt-6 max-w-none dark:prose-invert"
            v-html="renderedContent"
          />
          <div v-if="relatedGuides.length > 0" class="mt-12 border-t border-zinc-200 pt-8 dark:border-zinc-700">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Related guides</h2>
            <ul class="mt-3 space-y-2">
              <li v-for="rg in relatedGuides" :key="rg.id">
                <button
                  type="button"
                  class="text-sm font-medium text-zinc-600 underline decoration-zinc-300 underline-offset-2 hover:decoration-zinc-500 dark:text-zinc-400 dark:decoration-zinc-600 dark:hover:decoration-zinc-400"
                  @click="openGuideBySlug(rg.slug)"
                >
                  {{ rg.title }}
                </button>
              </li>
            </ul>
          </div>
        </div>

        <!-- Empty state when no guide selected -->
        <div
          v-else
          class="flex flex-col items-center justify-center rounded-xl border border-zinc-200 bg-white py-16 text-center dark:border-zinc-800 dark:bg-zinc-900"
        >
          <div class="mx-auto max-w-sm px-4">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Welcome to Help</h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
              Select a guide from the sidebar or use the search bar above to find documentation. You can also use the Help link on other admin pages to jump to relevant guides.
            </p>
            <p class="mt-4 text-xs text-zinc-500 dark:text-zinc-500">
              If you don't see any categories, an administrator needs to add guide categories and publish guides from Admin Guide → Categories and Guides.
            </p>
          </div>
        </div>
      </main>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { marked } from 'marked';
import PageContainer from '../components/PageContainer.vue';
import {
  getHelpCategories,
  getHelpGuides,
  getHelpGuideBySlug,
  searchHelp,
} from '../services/adminHelpService.js';

const route = useRoute();
const router = useRouter();

const helpCategories = ref([]);
const allGuides = ref([]);
const loadingCategories = ref(true);
const searchQuery = ref('');
const searchResults = ref([]);
const searching = ref(false);
const searchDebounce = ref(null);
const currentSlug = ref(null);
const currentGuide = ref(null);
const loadingGuide = ref(false);
const guideError = ref(null);

const guidesByCategory = computed(() => {
  const map = {};
  for (const cat of helpCategories.value) {
    map[cat.id] = allGuides.value.filter((g) => g.category_id === cat.id);
  }
  return map;
});

const relatedGuides = computed(() => {
  if (!currentGuide.value?.category_id) return [];
  return allGuides.value
    .filter((g) => g.category_id === currentGuide.value.category_id && g.slug !== currentGuide.value.slug)
    .slice(0, 5);
});

const renderedContent = computed(() => {
  if (!currentGuide.value?.content) return '';
  try {
    return marked(currentGuide.value.content, { gfm: true });
  } catch {
    return currentGuide.value.content;
  }
});

function formatDate(iso) {
  if (!iso) return '';
  try {
    const d = new Date(iso);
    return d.toLocaleDateString(undefined, { dateStyle: 'medium' });
  } catch {
    return iso;
  }
}

function openGuideBySlug(slug) {
  currentSlug.value = slug;
  router.replace({ path: route.path, query: { guide: slug } });
  loadGuide(slug);
  searchQuery.value = '';
}

function onSearchInput() {
  if (searchDebounce.value) clearTimeout(searchDebounce.value);
  const q = searchQuery.value.trim();
  if (!q) {
    searchResults.value = [];
    return;
  }
  searching.value = true;
  searchDebounce.value = setTimeout(async () => {
    try {
      searchResults.value = await searchHelp(q);
    } catch {
      searchResults.value = [];
    }
    searching.value = false;
    searchDebounce.value = null;
  }, 300);
}

async function loadGuide(slug) {
  loadingGuide.value = true;
  guideError.value = null;
  currentGuide.value = null;
  try {
    const data = await getHelpGuideBySlug(slug);
    currentGuide.value = data;
  } catch {
    guideError.value = 'Guide not found or not published.';
  } finally {
    loadingGuide.value = false;
  }
}

async function loadCategoriesAndGuides() {
  loadingCategories.value = true;
  try {
    const [cats, guides] = await Promise.all([
      getHelpCategories(),
      getHelpGuides(),
    ]);
    helpCategories.value = cats;
    allGuides.value = guides;
    const slugFromQuery = route.query.guide;
    if (slugFromQuery && typeof slugFromQuery === 'string') {
      currentSlug.value = slugFromQuery;
      await loadGuide(slugFromQuery);
    }
  } catch {
    helpCategories.value = [];
    allGuides.value = [];
  } finally {
    loadingCategories.value = false;
  }
}

watch(
  () => route.query.guide,
  (slug) => {
    if (slug && typeof slug === 'string' && slug !== currentSlug.value) {
      currentSlug.value = slug;
      loadGuide(slug);
    }
  }
);

onMounted(() => {
  loadCategoriesAndGuides();
});
</script>

<style scoped>
.guide-content :deep(h1) { @apply mt-8 text-xl font-bold first:mt-0; }
.guide-content :deep(h2) { @apply mt-6 text-lg font-semibold; }
.guide-content :deep(h3) { @apply mt-4 text-base font-semibold; }
.guide-content :deep(p) { @apply mt-2 leading-relaxed; }
.guide-content :deep(ul) { @apply mt-2 list-disc pl-6 space-y-1; }
.guide-content :deep(ol) { @apply mt-2 list-decimal pl-6 space-y-1; }
.guide-content :deep(blockquote) { @apply mt-2 border-l-4 border-zinc-300 pl-4 italic text-zinc-600 dark:border-zinc-600 dark:text-zinc-400; }
.guide-content :deep(pre) { @apply mt-2 overflow-x-auto rounded-lg bg-zinc-100 p-4 text-sm dark:bg-zinc-800; }
.guide-content :deep(code) { @apply rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-sm dark:bg-zinc-800; }
.guide-content :deep(pre code) { @apply bg-transparent p-0; }
.guide-content :deep(table) { @apply mt-2 w-full border-collapse text-sm; }
.guide-content :deep(th), .guide-content :deep(td) { @apply border border-zinc-200 px-3 py-2 text-left dark:border-zinc-700; }
.guide-content :deep(th) { @apply bg-zinc-50 font-semibold dark:bg-zinc-800; }
.guide-content :deep(a) { @apply text-zinc-700 underline hover:no-underline dark:text-zinc-300; }
</style>
