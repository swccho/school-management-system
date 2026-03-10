<template>
  <PageContainer
    title="Edit Guide"
    description="Update the help guide. Content supports Markdown."
  >
    <AdminGuideForm
      v-if="guideId != null"
      :guide-id="guideId"
      @saved="onSaved"
    />
    <div v-else class="p-4 text-sm text-zinc-500 dark:text-zinc-400">
      Loading…
    </div>
  </PageContainer>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import AdminGuideForm from '../components/AdminGuideForm.vue';
import { useToast } from '../../shared/composables/useToast.js';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const guideId = computed(() => route.params.id ?? null);

function onSaved() {
  toast.success('Guide updated successfully.');
  router.push({ name: 'admin-guide-guides' });
}
</script>
