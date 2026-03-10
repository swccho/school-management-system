<template>
  <PageContainer
    title="Add User"
    description="Create a new system user and assign roles."
  >
    <UserForm
      :user="null"
      :roles-options="rolesOptions"
      :linkable-options="linkableOptions"
      @saved="onSaved"
    />
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import UserForm from '../components/UserForm.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getRolesOptions, getLinkableEntitiesOptions } from '../services/userService.js';

const router = useRouter();
const toast = useToast();
const rolesOptions = ref([]);
const linkableOptions = ref({ staffs: [], students: [], guardians: [] });

async function loadRoles() {
  try {
    rolesOptions.value = await getRolesOptions();
  } catch {
    rolesOptions.value = [];
  }
}

async function loadLinkableOptions() {
  try {
    linkableOptions.value = await getLinkableEntitiesOptions();
  } catch {
    linkableOptions.value = { staffs: [], students: [], guardians: [] };
  }
}

function onSaved() {
  toast.success('User created successfully.');
  router.push({ name: 'users' });
}

onMounted(() => {
  loadRoles();
  loadLinkableOptions();
});
</script>
