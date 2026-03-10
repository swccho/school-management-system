<template>
  <PageContainer
    title="Banners"
    description="Manage homepage banners. Reorder and set status."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreateModal"
      >
        Add Banner
      </button>
    </template>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading banners…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="banners.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        No banners yet. Add one to get started.
      </div>
      <div v-else class="sidenav-scroll overflow-x-auto">
        <table class="w-full min-w-[600px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Preview</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Sort order</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="banner in banners"
              :key="banner.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3">
                <img
                  v-if="banner.image_url"
                  :src="banner.image_url"
                  :alt="banner.title"
                  class="h-14 w-24 rounded object-cover"
                />
                <span v-else class="text-xs text-zinc-400">—</span>
              </td>
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ banner.title }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ banner.sort_order }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="banner.status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                >
                  {{ banner.status }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap items-center gap-2">
                  <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="openEditModal(banner)"
                  >
                    Edit
                  </button>
                  <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  <button
                    type="button"
                    class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                    @click="confirmDelete(banner)"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <BannerForm
      v-model="modalOpen"
      :banner="editingBanner"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import BannerForm from '../components/BannerForm.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import { getList, deleteBanner } from '../services/bannerService.js';

const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

const loading = ref(true);
const error = ref(null);
const banners = ref([]);
const modalOpen = ref(false);
const editingBanner = ref(null);

function openCreateModal() {
  editingBanner.value = null;
  modalOpen.value = true;
}

function openEditModal(banner) {
  editingBanner.value = banner;
  modalOpen.value = true;
}

function onSaved() {
  fetchBanners();
  toast.success(editingBanner.value ? 'Banner updated successfully.' : 'Banner created successfully.');
}

async function fetchBanners() {
  loading.value = true;
  error.value = null;
  try {
    const data = await getList();
    banners.value = data;
  } catch {
    error.value = 'Failed to load banners.';
    banners.value = [];
  } finally {
    loading.value = false;
  }
}

async function confirmDelete(banner) {
  const confirmed = await openConfirmation({
    title: 'Confirm deletion',
    message: 'Are you sure you want to delete this banner?',
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await deleteBanner(banner.id);
    await fetchBanners();
    closeConfirmation();
    toast.success('Banner deleted successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to delete banner.');
  } finally {
    setConfirmationLoading(false);
  }
}

onMounted(() => {
  fetchBanners();
});
</script>
