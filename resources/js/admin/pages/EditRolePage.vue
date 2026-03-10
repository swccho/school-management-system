<template>
  <PageContainer
    title="Edit Role"
    description="Update role details."
  >
    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading role…
    </div>
    <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </div>
    <RoleForm
      v-else
      :role="role"
      @saved="onSaved"
    />
  </PageContainer>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import RoleForm from '../components/RoleForm.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getRole } from '../services/rolePermissionService.js';

const router = useRouter();
const route = useRoute();
const toast = useToast();
const role = ref(null);
const loading = ref(true);
const error = ref(null);

const roleId = computed(() => route.params.id);

async function loadRole() {
  if (!roleId.value) return;
  loading.value = true;
  error.value = null;
  try {
    role.value = await getRole(roleId.value);
  } catch {
    error.value = 'Failed to load role.';
    role.value = null;
  } finally {
    loading.value = false;
  }
}

function onSaved() {
  toast.success('Role updated successfully.');
  router.push({ name: 'roles' });
}

onMounted(() => {
  loadRole();
});
</script>
