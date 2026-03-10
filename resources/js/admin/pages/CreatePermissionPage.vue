<template>
  <PageContainer
    title="Add Permission"
    description="Create a new permission."
  >
    <PermissionForm @saved="onSaved" />
  </PageContainer>
</template>

<script setup>
import { useRouter } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import PermissionForm from '../components/PermissionForm.vue';
import { useToast } from '../../shared/composables/useToast.js';

const router = useRouter();
const toast = useToast();

function onSaved() {
  toast.success('Permission created successfully.');
  router.push({ name: 'permissions' });
}
</script>
