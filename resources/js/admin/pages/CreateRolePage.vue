<template>
  <PageContainer
    title="Add Role"
    description="Create a new role."
  >
    <RoleForm @saved="onSaved" />
  </PageContainer>
</template>

<script setup>
import { useRouter } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import RoleForm from '../components/RoleForm.vue';
import { useToast } from '../../shared/composables/useToast.js';

const router = useRouter();
const toast = useToast();

function onSaved() {
  toast.success('Role created successfully.');
  router.push({ name: 'roles' });
}
</script>
