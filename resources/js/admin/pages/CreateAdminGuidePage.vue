<template>
  <PageContainer
    title="Add Guide"
    description="Create a new help guide. Content supports Markdown."
  >
    <AdminGuideForm @saved="onSaved" />
  </PageContainer>
</template>

<script setup>
import { useRouter } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import AdminGuideForm from '../components/AdminGuideForm.vue';
import { useToast } from '../../shared/composables/useToast.js';

const router = useRouter();
const toast = useToast();

function onSaved() {
  toast.success('Guide created successfully.');
  router.push({ name: 'admin-guide-guides' });
}
</script>
