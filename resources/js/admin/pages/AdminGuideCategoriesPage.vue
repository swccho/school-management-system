<template>
  <PageContainer
    title="Guide Categories"
    description="Organize help articles into categories. Reorder and set status."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreateModal"
      >
        Add Category
      </button>
    </template>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading categories…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="categories.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        No categories yet. Add one to get started.
      </div>
      <div v-else class="sidenav-scroll overflow-x-auto">
        <table class="w-full min-w-[600px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="w-10 px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Order</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Slug</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Sort Order</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(cat, index) in categories"
              :key="cat.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3">
                <div class="flex items-center gap-1">
                  <button
                    type="button"
                    class="rounded p-1 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200 disabled:opacity-40"
                    :disabled="index === 0"
                    title="Move up"
                    @click="moveUp(index)"
                  >
                    ↑
                  </button>
                  <button
                    type="button"
                    class="rounded p-1 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200 disabled:opacity-40"
                    :disabled="index === categories.length - 1"
                    title="Move down"
                    @click="moveDown(index)"
                  >
                    ↓
                  </button>
                </div>
              </td>
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ cat.name }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ cat.slug }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                  :class="cat.status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                >
                  {{ cat.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ cat.sort_order }}</td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap items-center gap-2">
                  <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="openEditModal(cat)"
                  >
                    Edit
                  </button>
                  <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  <button
                    type="button"
                    class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                    @click="confirmDelete(cat)"
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

    <AdminGuideCategoryForm
      v-model="modalOpen"
      :category="editingCategory"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import AdminGuideCategoryForm from '../components/AdminGuideCategoryForm.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import {
  getCategories,
  deleteCategory,
  reorderCategoryIds,
} from '../services/adminGuideCategoryService.js';

const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

const loading = ref(true);
const error = ref(null);
const categories = ref([]);
const modalOpen = ref(false);
const editingCategory = ref(null);

function openCreateModal() {
  editingCategory.value = null;
  modalOpen.value = true;
}

function openEditModal(cat) {
  editingCategory.value = cat;
  modalOpen.value = true;
}

function onSaved() {
  fetchCategories();
  toast.success(editingCategory.value ? 'Category updated successfully.' : 'Category created successfully.');
}

async function fetchCategories() {
  loading.value = true;
  error.value = null;
  try {
    const data = await getCategories();
    categories.value = data;
  } catch {
    error.value = 'Failed to load categories.';
    categories.value = [];
  } finally {
    loading.value = false;
  }
}

async function moveUp(index) {
  if (index <= 0) return;
  const next = [...categories.value];
  [next[index - 1], next[index]] = [next[index], next[index - 1]];
  await saveOrder(next.map((c) => c.id));
}

async function moveDown(index) {
  if (index >= categories.value.length - 1) return;
  const next = [...categories.value];
  [next[index], next[index + 1]] = [next[index + 1], next[index]];
  await saveOrder(next.map((c) => c.id));
}

async function saveOrder(ids) {
  try {
    await reorderCategoryIds(ids);
    await fetchCategories();
    toast.success('Order updated.');
  } catch {
    toast.error('Failed to update order.');
  }
}

async function confirmDelete(cat) {
  const confirmed = await openConfirmation({
    title: 'Confirm deletion',
    message: 'Are you sure you want to delete this category? Guides in it will also be deleted.',
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await deleteCategory(cat.id);
    await fetchCategories();
    closeConfirmation();
    toast.success('Category deleted successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to delete category.');
  } finally {
    setConfirmationLoading(false);
  }
}

onMounted(() => {
  fetchCategories();
});
</script>
