<template>
  <PageContainer
    title="Edit User"
    description="Update user information and roles."
  >
    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading user…
    </div>
    <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </div>
    <UserForm
      v-else
      :user="user"
      :roles-options="rolesOptions"
      :linkable-options="linkableOptions"
      @saved="onSaved"
    />
  </PageContainer>
</template>

<script setup>
import { onMounted, ref, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import UserForm from '../components/UserForm.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getUser, getRolesOptions, getLinkableEntitiesOptions } from '../services/userService.js';

const router = useRouter();
const route = useRoute();
const toast = useToast();
const user = ref(null);
const rolesOptions = ref([]);
const linkableOptions = ref({ staffs: [], students: [], guardians: [] });
const loading = ref(true);
const error = ref(null);

const userId = computed(() => route.params.id);

async function loadUser() {
  if (!userId.value) return;
  loading.value = true;
  error.value = null;
  try {
    user.value = await getUser(userId.value);
  } catch {
    error.value = 'Failed to load user.';
    user.value = null;
  } finally {
    loading.value = false;
  }
}

async function loadRoles() {
  try {
    rolesOptions.value = await getRolesOptions();
  } catch {
    rolesOptions.value = [];
  }
}

async function loadLinkableOptions() {
  if (!userId.value) return;
  try {
    linkableOptions.value = await getLinkableEntitiesOptions(userId.value);
  } catch {
    linkableOptions.value = { staffs: [], students: [], guardians: [] };
  }
}

function onSaved() {
  toast.success('User updated successfully.');
  router.push({ name: 'users' });
}

onMounted(() => {
  loadRoles();
  loadUser();
});

watch(user, (u) => {
  if (u?.id) {
    loadLinkableOptions();
  }
}, { immediate: true });
</script>
