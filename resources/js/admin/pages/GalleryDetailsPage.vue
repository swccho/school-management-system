<template>
  <PageContainer
    :title="gallery?.title ?? 'Gallery'"
    description="Manage gallery items. Upload, reorder, and edit captions."
  >
    <template #actions>
      <router-link
        :to="{ name: 'galleries' }"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        Back to list
      </router-link>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openEditGalleryModal"
      >
        Edit gallery
      </button>
    </template>

    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading gallery…
    </div>
    <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </div>
    <template v-else-if="gallery">
      <div class="mb-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <p v-if="gallery.description" class="text-sm text-zinc-600 dark:text-zinc-400">{{ gallery.description }}</p>
        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
          {{ gallery.items_count ?? gallery.items?.length ?? 0 }} items · {{ gallery.status }} · {{ gallery.published_at_formatted ?? 'Not published' }}
        </p>
      </div>

      <div class="mb-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Add item</h3>
        <div class="mt-2 flex flex-wrap items-end gap-4">
          <div class="min-w-[200px]">
            <input
              ref="fileInputRef"
              type="file"
              accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm"
              class="block w-full text-sm text-zinc-600 file:mr-2 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-zinc-700 dark:text-zinc-400 dark:file:bg-zinc-700 dark:file:text-zinc-200"
              @change="onFileSelect"
            />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Image or video. Max 10MB.</p>
          </div>
          <div class="min-w-[180px]" :class="{ 'opacity-50': !selectedFile }">
            <label for="item-caption" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Caption</label>
            <input
              id="item-caption"
              v-model="newItemCaption"
              type="text"
              placeholder="Optional"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <button
            type="button"
            class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-50 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            :disabled="uploading || !selectedFile"
            @click="uploadItem"
          >
            {{ uploading ? 'Uploading…' : 'Upload' }}
          </button>
        </div>
      </div>

      <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mb-3 flex items-center justify-between">
          <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Items</h3>
          <button
            v-if="items.length > 0"
            type="button"
            class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
            @click="saveReorder"
          >
            {{ reorderDirty ? 'Save order' : 'Reorder' }}
          </button>
        </div>
        <div v-if="items.length === 0" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          No items yet. Upload an image or video above.
        </div>
        <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
          <div
            v-for="(item, index) in items"
            :key="item.id"
            class="relative rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden bg-zinc-50 dark:bg-zinc-800/50"
          >
            <div class="aspect-square flex items-center justify-center bg-zinc-100 dark:bg-zinc-800">
              <img
                v-if="item.media_type === 'image'"
                :src="item.url"
                :alt="item.caption || 'Item'"
                class="h-full w-full object-cover"
              />
              <video
                v-else
                :src="item.url"
                class="max-h-full max-w-full object-contain"
                controls
                muted
                preload="metadata"
              />
            </div>
            <div class="p-2">
              <input
                v-if="editingCaptionId === item.id"
                v-model="editCaptionValue"
                type="text"
                class="block w-full rounded border border-zinc-300 px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                placeholder="Caption"
                @keyup.enter="saveCaption(item)"
                @keyup.escape="editingCaptionId = null"
              />
              <p v-else class="truncate text-xs text-zinc-600 dark:text-zinc-400">
                {{ item.caption || '—' }}
              </p>
            </div>
            <div class="absolute right-2 top-2 flex gap-1">
              <button
                type="button"
                class="rounded bg-white/90 px-2 py-1 text-xs font-medium text-zinc-700 shadow hover:bg-white dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
                @click="startEditCaption(item)"
              >
                Edit
              </button>
              <button
                type="button"
                class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-700 shadow hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60"
                @click="confirmDeleteItem(item)"
              >
                Delete
              </button>
            </div>
            <div v-if="reorderDirty" class="absolute left-2 top-2 flex flex-col gap-0.5">
              <button
                type="button"
                class="rounded bg-white/90 p-1 shadow disabled:opacity-50 dark:bg-zinc-800"
                :disabled="index === 0"
                aria-label="Move up"
                @click="moveItem(index, -1)"
              >
                <span class="text-xs">↑</span>
              </button>
              <button
                type="button"
                class="rounded bg-white/90 p-1 shadow disabled:opacity-50 dark:bg-zinc-800"
                :disabled="index === items.length - 1"
                aria-label="Move down"
                @click="moveItem(index, 1)"
              >
                <span class="text-xs">↓</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>

    <GalleryForm
      v-model="editGalleryModalOpen"
      :gallery="gallery"
      @saved="onGallerySaved"
      @close="editGalleryModalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import GalleryForm from '../components/GalleryForm.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import {
  getOne,
  addItem,
  updateItem,
  deleteItem,
  reorderItems,
} from '../services/galleryService.js';

const route = useRoute();
const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

const loading = ref(true);
const error = ref(null);
const gallery = ref(null);
const uploading = ref(false);
const selectedFile = ref(null);
const newItemCaption = ref('');
const fileInputRef = ref(null);
const editingCaptionId = ref(null);
const editCaptionValue = ref('');
const reorderDirty = ref(false);
const editGalleryModalOpen = ref(false);

const items = computed(() => {
  const list = gallery.value?.items ?? [];
  if (!reorderDirty.value || !localOrder.value.length) return list;
  return localOrder.value.map((id) => list.find((i) => i.id === id)).filter(Boolean);
});

const localOrder = ref([]);

function startReorder() {
  const list = gallery.value?.items ?? [];
  localOrder.value = list.map((i) => i.id);
  reorderDirty.value = true;
}

function moveItem(index, delta) {
  if (!reorderDirty.value) startReorder();
  const next = index + delta;
  if (next < 0 || next >= localOrder.value.length) return;
  const arr = [...localOrder.value];
  [arr[index], arr[next]] = [arr[next], arr[index]];
  localOrder.value = arr;
}

async function saveReorder() {
  if (!reorderDirty.value) {
    startReorder();
    return;
  }
  setConfirmationLoading(true);
  try {
    await reorderItems(localOrder.value);
    await fetchGallery();
    reorderDirty.value = false;
    localOrder.value = [];
    closeConfirmation();
    toast.success('Order saved.');
  } catch {
    closeConfirmation();
    toast.error('Failed to save order.');
  } finally {
    setConfirmationLoading(false);
  }
}

async function fetchGallery() {
  const id = route.params.id;
  if (!id) return;
  loading.value = true;
  error.value = null;
  try {
    gallery.value = await getOne(id);
    reorderDirty.value = false;
    localOrder.value = [];
  } catch {
    error.value = 'Failed to load gallery.';
    gallery.value = null;
  } finally {
    loading.value = false;
  }
}

function onFileSelect(e) {
  const file = e.target.files?.[0];
  selectedFile.value = file ?? null;
}

async function uploadItem() {
  if (!selectedFile.value || !gallery.value?.id) return;
  uploading.value = true;
  try {
    await addItem(gallery.value.id, {
      file: selectedFile.value,
      caption: newItemCaption.value?.trim() || null,
    });
    await fetchGallery();
    selectedFile.value = null;
    newItemCaption.value = '';
    if (fileInputRef.value) fileInputRef.value.value = '';
    toast.success('Item added.');
  } catch (err) {
    const msg = err.response?.data?.message || 'Upload failed.';
    toast.error(msg);
  } finally {
    uploading.value = false;
  }
}

function startEditCaption(item) {
  editingCaptionId.value = item.id;
  editCaptionValue.value = item.caption ?? '';
}

async function saveCaption(item) {
  try {
    await updateItem(item.id, { caption: editCaptionValue.value?.trim() || null });
    if (gallery.value?.items) {
      const idx = gallery.value.items.findIndex((i) => i.id === item.id);
      if (idx !== -1) {
        gallery.value.items[idx] = { ...gallery.value.items[idx], caption: editCaptionValue.value?.trim() || null };
      }
    }
    editingCaptionId.value = null;
    toast.success('Caption updated.');
  } catch {
    toast.error('Failed to update caption.');
  }
}

async function confirmDeleteItem(item) {
  const confirmed = await openConfirmation({
    title: 'Delete item',
    message: 'Remove this item from the gallery?',
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await deleteItem(item.id);
    await fetchGallery();
    closeConfirmation();
    toast.success('Item deleted.');
  } catch {
    closeConfirmation();
    toast.error('Failed to delete item.');
  } finally {
    setConfirmationLoading(false);
  }
}

function openEditGalleryModal() {
  editGalleryModalOpen.value = true;
}

function onGallerySaved() {
  fetchGallery();
  toast.success('Gallery updated.');
}

watch(
  () => route.params.id,
  (id) => {
    if (id) fetchGallery();
  },
  { immediate: true }
);
</script>
