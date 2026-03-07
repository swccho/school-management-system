<template>
  <PageContainer
    title="Generate Results"
    description="Generate exam results from saved marks. Select an exam and optional filters, then run generation."
  >
    <template #actions>
      <router-link
        to="/admin/results"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        ← Back to results
      </router-link>
    </template>

    <div class="space-y-6">
      <ResultGenerationForm
        v-model="context"
        @change="context = $event"
      />
      <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <p class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Summary</p>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">
          Results will be computed from all saved marks for the selected exam. If you choose a class or section, only students with marks in that context are included. Grade scale (or default) is used for letter grade and GPA. Regenerating overwrites existing results for the same students.
        </p>
      </div>
      <div>
        <button
          type="button"
          :disabled="!context.exam_id || generating"
          class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-50 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
          @click="confirmThenGenerate"
        >
          {{ generating ? 'Generating…' : 'Generate results' }}
        </button>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import ResultGenerationForm from '../components/ResultGenerationForm.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import { generateResults } from '../services/resultService.js';

const router = useRouter();
const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

const context = ref({
  exam_id: null,
  class_id: null,
  section_id: null,
  grade_scale_id: null,
});
const generating = ref(false);

async function confirmThenGenerate() {
  if (!context.value.exam_id || generating.value) return;
  const confirmed = await openConfirmation({
    title: 'Generate results',
    message: 'Results will be computed from saved marks. Regenerating overwrites existing results for the same students. Continue?',
    confirmLabel: 'Generate',
    cancelLabel: 'Cancel',
    variant: 'destructive',
  });
  if (!confirmed) return;
  await generate();
}

async function generate() {
  generating.value = true;
  setConfirmationLoading(true);
  try {
    const result = await generateResults({
      exam_id: context.value.exam_id,
      class_id: context.value.class_id || undefined,
      section_id: context.value.section_id || undefined,
      grade_scale_id: context.value.grade_scale_id || undefined,
    });
    closeConfirmation();
    const msg = result.message + (result.generated != null ? ` (${result.generated} student(s)).` : '');
    toast.success(msg);
    if (result.generated > 0) {
      setTimeout(() => router.push('/admin/results'), 1500);
    }
  } catch (e) {
    closeConfirmation();
    toast.error(e.response?.data?.message || 'Failed to generate results.');
  } finally {
    generating.value = false;
    setConfirmationLoading(false);
  }
}
</script>
