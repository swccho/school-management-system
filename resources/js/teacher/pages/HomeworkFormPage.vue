<template>
  <PageContainer :title="isEdit ? 'Edit Homework' : 'Add Homework'" :description="isEdit ? 'Update homework details.' : 'Create homework for an assigned class/section/subject.'">
    <form class="max-w-xl space-y-4 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900" @submit.prevent="submit">
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Class · Section · Subject</label>
        <select v-model="form.classSectionSubject" required class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" :disabled="isEdit">
          <option value="">Select</option>
          <option v-for="a in assignments" :key="a.class_id + '-' + a.section_id + '-' + a.subject_id" :value="a.class_id + '-' + a.section_id + '-' + a.subject_id">
            {{ a.class_name }} · {{ a.section_name }} · {{ a.subject_name }}
          </option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</label>
        <input v-model="form.title" type="text" required maxlength="255" class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
      </div>
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
        <textarea v-model="form.description" rows="3" class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Due date</label>
        <input v-model="form.due_date" type="date" class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
      </div>
      <div v-if="!isEdit">
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Attachment (optional)</label>
        <input type="file" class="mt-1 w-full text-sm" accept=".pdf,.doc,.docx,image/*" @change="form.attachment = $event.target.files?.[0]" />
      </div>
      <div class="flex gap-3">
        <button type="submit" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900" :disabled="saving">
          {{ saving ? 'Saving…' : (isEdit ? 'Update' : 'Create') }}
        </button>
        <router-link to="/teacher/homework" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600">Cancel</router-link>
      </div>
      <p v-if="error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </form>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { useAuthStore } from '../stores/authStore.js';
import { getHomework, createHomework, updateHomework } from '../services/homeworkService.js';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const isEdit = computed(() => !!route.params.id);
const assignments = computed(() => authStore.assignments ?? []);
const saving = ref(false);
const error = ref('');

const form = ref({
  classSectionSubject: '',
  title: '',
  description: '',
  due_date: '',
  attachment: null,
});

onMounted(async () => {
  if (isEdit.value) {
    try {
      const h = await getHomework(route.params.id);
      form.value.title = h.title;
      form.value.description = h.description ?? '';
      form.value.due_date = h.due_date ?? '';
      form.value.classSectionSubject = [h.class_id, h.section_id, h.subject_id].join('-');
    } catch {
      error.value = 'Failed to load homework.';
    }
  }
});

async function submit() {
  error.value = '';
  const [class_id, section_id, subject_id] = form.value.classSectionSubject.split('-').map(Number);
  const payload = {
    class_id,
    section_id,
    subject_id,
    title: form.value.title,
    description: form.value.description || null,
    due_date: form.value.due_date || null,
    attachment: form.value.attachment,
  };
  saving.value = true;
  try {
    if (isEdit.value) {
      await updateHomework(route.params.id, payload);
      router.push('/teacher/homework');
    } else {
      await createHomework(payload);
      router.push('/teacher/homework');
    }
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to save.';
  } finally {
    saving.value = false;
  }
}
</script>
