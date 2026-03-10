<template>
  <PageContainer title="Leave Request" description="Submit and view your leave requests.">
    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
      <h3 class="mb-4 text-lg font-medium">{{ editingId ? 'Edit leave request' : 'Submit leave request' }}</h3>
      <form class="max-w-md space-y-4" @submit.prevent="editingId ? updateRequest() : submitRequest()">
        <div>
          <label for="leave-type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Leave type</label>
          <input id="leave-type" v-model="form.leave_type" type="text" required maxlength="100" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" placeholder="e.g. Sick leave" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="start-date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Start date</label>
            <input id="start-date" v-model="form.start_date" type="date" required class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
          </div>
          <div>
            <label for="end-date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">End date</label>
            <input id="end-date" v-model="form.end_date" type="date" required class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
          </div>
        </div>
        <div>
          <label for="reason" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Reason</label>
          <textarea id="reason" v-model="form.reason" required rows="3" maxlength="1000" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800"></textarea>
        </div>
        <div>
          <label for="attachment" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Attachment (optional)</label>
          <input id="attachment" type="file" class="mt-1 w-full text-sm" accept=".pdf,image/*" @change="form.attachment = $event.target.files?.[0]" />
        </div>
        <div class="flex gap-2">
          <button type="submit" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900" :disabled="saving">
            {{ saving ? (editingId ? 'Updating…' : 'Submitting…') : (editingId ? 'Update request' : 'Submit request') }}
          </button>
          <button v-if="editingId" type="button" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600" @click="cancelEdit">Cancel</button>
        </div>
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
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
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
            <td class="px-4 py-3">
              <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="min-h-[44px] min-w-[44px] rounded-lg px-3 py-2 text-sm text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100" @click="openDetail(l.id)">View</button>
                <template v-if="l.status === 'pending'">
                  <button type="button" class="min-h-[44px] min-w-[44px] rounded-lg px-3 py-2 text-sm text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100" @click="startEdit(l.id)">Edit</button>
                  <button type="button" class="min-h-[44px] min-w-[44px] rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-900/20" @click="confirmCancel(l.id)">Cancel</button>
                </template>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detail modal -->
    <div v-if="detail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="detail = null">
      <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900" @click.stop>
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Leave request details</h3>
        <dl class="mt-4 space-y-2 text-sm">
          <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Type</dt><dd class="text-zinc-900 dark:text-zinc-100">{{ detail.leave_type }}</dd></div>
          <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Start – End</dt><dd class="text-zinc-900 dark:text-zinc-100">{{ detail.start_date }} – {{ detail.end_date }}</dd></div>
          <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Status</dt><dd><span class="rounded px-2 py-0.5 text-xs font-medium" :class="statusClass(detail.status)">{{ detail.status }}</span></dd></div>
          <div><dt class="font-medium text-zinc-500 dark:text-zinc-400">Reason</dt><dd class="text-zinc-900 dark:text-zinc-100">{{ detail.reason }}</dd></div>
          <div v-if="detail.remarks"><dt class="font-medium text-zinc-500 dark:text-zinc-400">Remarks</dt><dd class="text-zinc-900 dark:text-zinc-100">{{ detail.remarks }}</dd></div>
          <div v-if="detail.attachment_url">
            <dt class="font-medium text-zinc-500 dark:text-zinc-400">Attachment</dt>
            <dd><a :href="detail.attachment_url" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline dark:text-blue-400">Download attachment</a></dd>
          </div>
        </dl>
        <button type="button" class="mt-4 rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600" @click="detail = null">Close</button>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import {
  getLeaveRequests,
  getLeaveRequest,
  createLeaveRequest,
  updateLeaveRequest,
  deleteLeaveRequest,
} from '../services/leaveRequestService.js';

const list = ref([]);
const loadingList = ref(true);
const saving = ref(false);
const submitError = ref('');
const editingId = ref(null);
const detail = ref(null);

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
  if (s === 'cancelled') return 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400';
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

async function openDetail(id) {
  try {
    detail.value = await getLeaveRequest(id);
  } catch {
    detail.value = null;
  }
}

function startEdit(id) {
  const l = list.value.find((x) => x.id === id);
  if (!l || l.status !== 'pending') return;
  editingId.value = id;
  form.value = {
    leave_type: l.leave_type,
    start_date: l.start_date,
    end_date: l.end_date,
    reason: l.reason,
    attachment: null,
  };
  submitError.value = '';
}

function cancelEdit() {
  editingId.value = null;
  form.value = { leave_type: '', start_date: '', end_date: '', reason: '', attachment: null };
  submitError.value = '';
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

async function updateRequest() {
  if (!editingId.value) return;
  submitError.value = '';
  saving.value = true;
  try {
    await updateLeaveRequest(editingId.value, { ...form.value });
    editingId.value = null;
    form.value = { leave_type: '', start_date: '', end_date: '', reason: '', attachment: null };
    loadList();
    detail.value = null;
  } catch (e) {
    submitError.value = e.response?.data?.message ?? 'Failed to update.';
  } finally {
    saving.value = false;
  }
}

function confirmCancel(id) {
  if (!window.confirm('Cancel this leave request? This cannot be undone.')) return;
  cancelLeaveRequest(id);
}

async function cancelLeaveRequest(id) {
  try {
    await deleteLeaveRequest(id);
    loadList();
    if (detail.value?.id === id) detail.value = null;
    if (editingId.value === id) cancelEdit();
  } catch (e) {
    alert(e.response?.data?.message ?? 'Failed to cancel.');
  }
}

onMounted(loadList);
</script>
