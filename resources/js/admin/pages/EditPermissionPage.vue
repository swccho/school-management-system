<template>
  <PageContainer
    title="Edit Permission"
    description="Update permission details."
  >
    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading permission…
    </div>
    <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </div>
    <PermissionForm
      v-else
      :permission="permission"
      @saved="onSaved"
    />
  </PageContainer>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import PermissionForm from '../components/PermissionForm.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getPermission } from '../services/rolePermissionService.js';

const router = useRouter();
const route = useRoute();
const toast = useToast();
const permission = ref(null);
const loading = ref(true);
const error = ref(null);

const permissionId = computed(() => route.params.id);

async function loadPermission() {
  if (!permissionId.value) return;
  loading.value = true;
  error.value = null;
  try {
    permission.value = await getPermission(permissionId.value);
  } catch {
    error.value = 'Failed to load permission.';
    permission.value = null;
  } finally {
    loading.value = false;
  }
}

function onSaved() {
  toast.success('Permission updated successfully.');
  router.push({ name: 'permissions' });
}

onMounted(() => {
  loadPermission();
});
</script>
