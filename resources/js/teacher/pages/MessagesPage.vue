<template>
  <PageContainer title="Messages" description="Send and receive messages.">
    <div class="mb-4 flex flex-wrap items-center gap-3">
      <button
        type="button"
        class="min-h-[44px] rounded-lg bg-zinc-800 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600"
        @click="showCompose = true"
      >
        New message
      </button>
      <div class="flex gap-2">
        <button
          type="button"
          :class="folder === 'inbox' ? 'bg-zinc-200 dark:bg-zinc-700' : 'bg-white dark:bg-zinc-800'"
          class="rounded-lg border border-zinc-300 px-3 py-2 text-sm font-medium dark:border-zinc-600"
          @click="folder = 'inbox'; load()"
        >
          Inbox
        </button>
        <button
          type="button"
          :class="folder === 'sent' ? 'bg-zinc-200 dark:bg-zinc-700' : 'bg-white dark:bg-zinc-800'"
          class="rounded-lg border border-zinc-300 px-3 py-2 text-sm font-medium dark:border-zinc-600"
          @click="folder = 'sent'; load()"
        >
          Sent
        </button>
      </div>
    </div>
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="!list.length" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      No conversations yet.
    </div>
    <div v-else class="space-y-2">
      <button
        v-for="c in list"
        :key="c.id"
        type="button"
        class="w-full rounded-xl border border-zinc-200 bg-white p-4 text-left transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700"
        @click="openThread(c.id)"
      >
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0 flex-1">
            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ c.other_participant_name }}</p>
            <p v-if="c.subject" class="text-xs text-zinc-500 dark:text-zinc-400">{{ c.subject }}</p>
            <p class="mt-1 truncate text-sm text-zinc-600 dark:text-zinc-400">{{ c.last_message_preview || '—' }}</p>
          </div>
          <div class="shrink-0 text-right">
            <span v-if="c.unread_count" class="rounded-full bg-blue-600 px-2 py-0.5 text-xs font-medium text-white">{{ c.unread_count }}</span>
            <p class="mt-1 text-xs text-zinc-500">{{ formatDate(c.last_message_at) }}</p>
          </div>
        </div>
      </button>
    </div>

    <!-- Compose modal -->
    <div v-if="showCompose" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showCompose = false">
      <div class="w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900" @click.stop>
        <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">New message</h3>
        <form @submit.prevent="sendNewMessage">
          <div class="space-y-4">
            <div>
              <label for="compose-recipient" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">To</label>
              <select id="compose-recipient" v-model="compose.recipient_id" required class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                <option value="">Select recipient</option>
                <option v-for="r in recipients" :key="r.id" :value="r.id">{{ r.name }} ({{ r.email }})</option>
              </select>
            </div>
            <div>
              <label for="compose-subject" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject (optional)</label>
              <input id="compose-subject" v-model="compose.subject" type="text" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" maxlength="255" />
            </div>
            <div>
              <label for="compose-body" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Message</label>
              <textarea id="compose-body" v-model="compose.body" required rows="4" class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" maxlength="5000"></textarea>
            </div>
            <div>
              <label for="compose-attachments" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Attachments (optional)</label>
              <input id="compose-attachments" type="file" class="mt-1 w-full text-sm" accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx" multiple @change="compose.attachments = Array.from($event.target.files || [])" />
            </div>
          </div>
          <div class="mt-4 flex gap-2">
            <button type="submit" class="rounded-lg bg-zinc-800 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-700" :disabled="sending">{{ sending ? 'Sending…' : 'Send' }}</button>
            <button type="button" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600" @click="showCompose = false">Cancel</button>
          </div>
          <p v-if="composeError" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ composeError }}</p>
        </form>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getRecipients, getConversations, sendMessage } from '../services/messageService.js';

const router = useRouter();
const loading = ref(true);
const list = ref([]);
const folder = ref('inbox');
const showCompose = ref(false);
const recipients = ref([]);
const compose = ref({ recipient_id: '', subject: '', body: '', attachments: [] });
const sending = ref(false);
const composeError = ref('');

function formatDate(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  return d.toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' });
}

async function load() {
  loading.value = true;
  try {
    const res = await getConversations({ folder: folder.value, per_page: 20 });
    list.value = res.data ?? [];
  } catch {
    list.value = [];
  } finally {
    loading.value = false;
  }
}

function openThread(id) {
  router.push({ name: 'teacher-message-thread', params: { id } });
}

async function loadRecipients() {
  try {
    recipients.value = await getRecipients();
  } catch {
    recipients.value = [];
  }
}

async function sendNewMessage() {
  composeError.value = '';
  sending.value = true;
  try {
    const res = await sendMessage({
      recipient_id: compose.value.recipient_id,
      subject: compose.value.subject,
      body: compose.value.body,
      attachments: compose.value.attachments,
    });
    showCompose.value = false;
    compose.value = { recipient_id: '', subject: '', body: '', attachments: [] };
    load();
    if (res?.conversation?.id) {
      router.push({ name: 'teacher-message-thread', params: { id: res.conversation.id } });
    }
  } catch (e) {
    composeError.value = e.response?.data?.message ?? 'Failed to send.';
  } finally {
    sending.value = false;
  }
}

onMounted(() => {
  load();
  loadRecipients();
});
</script>
