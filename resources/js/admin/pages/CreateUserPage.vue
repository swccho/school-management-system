<template>
  <PageContainer
    title="Add User"
    description="Create a new system user and assign roles."
  >
    <UserForm
      :user="null"
      :roles-options="rolesOptions"
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
import { getRolesOptions } from '../services/userService.js';

const router = useRouter();
const toast = useToast();
const rolesOptions = ref([]);

async function loadRoles() {
  try {
    rolesOptions.value = await getRolesOptions();
  } catch {
    rolesOptions.value = [];
  }
}

function onSaved() {
  toast.success('User created successfully.');
  router.push({ name: 'users' });
}

onMounted(() => {
  loadRoles();
});
</script>
