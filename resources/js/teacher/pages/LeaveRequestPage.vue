<template>
  <PageContainer title="Leave Request" description="Submit and view your leave requests.">
    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
      <h3 class="mb-4 text-lg font-medium">Submit leave request</h3>
      <form class="max-w-md space-y-4" @submit.prevent="submitRequest">
        <div>
          <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Leave type</label>
          <input v-model="form.leave_type" type="text" required maxlength="100" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" placeholder="e.g. Sick leave" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Start date</label>
            <input v-model="form.start_date" type="date" required class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
          </div>
          <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">End date</label>
            <input v-model="form.end_date" type="date" required class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Reason</label>
          <textarea v-model="form.reason" required rows="3" maxlength="1000" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Attachment (optional)</label>
          <input type="file" class="mt-1 w-full text-sm" accept=".pdf,image/*" @change="form.attachment = $event.target.files?.[0]" />
        </div>
        <button type="submit" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900" :disabled="saving">
          {{ saving ? 'Submitting…' : 'Submit request' }}
        </button>
        <p v-if="submitError" class="text-sm text-red-600 dark:text-red-400">{{ submitError }}</p>
      </form>
    </div>

    <h3 class="mb-3 text-lg font-medium">Your leave requests</h3>
    <div v-if="loadingList" class="py-4 text-sm text-zinc-500">Loading…</div>
    <div v-else-if="list.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      No leave requests yet.
    </div>
    <div v-else class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <table class="w-full min-w-[400px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Start – End</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Remarks</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="l in list" :key="l.id" class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0">
            <td class="px-4 py-3 text-sm">{{ l.leave_type }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ l.start_date }} – {{ l.end_date }}</td>
            <td class="px-4 py-3">
              <span class="rounded px-2 py-0.5 text-xs font-medium" :class="statusClass(l.status)">{{ l.status }}</span>
            </td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ l.remarks ?? '—' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { getLeaveRequests, createLeaveRequest } from '../services/leaveRequestService.js';

const list = ref([]);
const loadingList = ref(true);
const saving = ref(false);
const submitError = ref('');

const form = ref({
  leave_type: '',
  start_date: '',
  end_date: '',
  reason: '',
  attachment: null,
});

function statusClass(s) {
  if (s === 'approved') return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
  if (s === 'rejected') return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
  return 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300';
}

async function loadList() {
  loadingList.value = true;
  try {
    list.value = await getLeaveRequests();
  } catch {
    list.value = [];
  } finally {
    loadingList.value = false;
  }
}

async function submitRequest() {
  submitError.value = '';
  saving.value = true;
  try {
    const payload = { ...form.value };
    if (payload.attachment instanceof File) {
      const fd = new FormData();
      fd.append('leave_type', payload.leave_type);
      fd.append('start_date', payload.start_date);
      fd.append('end_date', payload.end_date);
      fd.append('reason', payload.reason);
      fd.append('attachment', payload.attachment);
      await createLeaveRequest(fd);
    } else {
      await createLeaveRequest(payload);
    }
    form.value = { leave_type: '', start_date: '', end_date: '', reason: '', attachment: null };
    loadList();
  } catch (e) {
    submitError.value = e.response?.data?.message ?? 'Failed to submit.';
  } finally {
    saving.value = false;
  }
}

onMounted(loadList);
</script>
