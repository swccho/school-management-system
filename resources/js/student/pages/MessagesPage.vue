<template>
  <PageContainer title="Messages" description="Message your teachers.">
    <div class="flex flex-col gap-4 lg:flex-row lg:gap-6">
      <div class="w-full shrink-0 lg:w-80">
        <div class="flex items-center justify-between gap-2">
          <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Conversations</h3>
          <button
            type="button"
            class="rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            @click="showCompose = true"
          >
            New message
          </button>
        </div>
        <div v-if="conversationsLoading" class="mt-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">Loading…</div>
        <div v-else-if="!conversations.length" class="mt-3 rounded-xl border border-zinc-200 bg-white p-6 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
          No conversations yet. Start by sending a message to a teacher.
        </div>
        <div v-else class="mt-3 space-y-1">
          <button
            v-for="c in conversations"
            :key="c.id"
            type="button"
            class="w-full rounded-lg border p-3 text-left transition"
            :class="selectedId === c.id
              ? 'border-zinc-900 bg-zinc-100 dark:border-zinc-100 dark:bg-zinc-800'
              : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700'"
            @click="selectConversation(c.id)"
          >
            <div class="flex items-start justify-between gap-2">
              <p class="truncate font-medium text-zinc-900 dark:text-zinc-100">{{ c.other_participant_name }}</p>
              <span v-if="c.unread_count" class="shrink-0 rounded-full bg-blue-600 px-2 py-0.5 text-xs font-medium text-white">{{ c.unread_count }}</span>
            </div>
            <p class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">{{ c.last_message_preview || '—' }}</p>
          </button>
        </div>
      </div>

      <div class="min-w-0 flex-1 rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <template v-if="!selectedId">
          <div class="flex flex-col items-center justify-center p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
            <p>Select a conversation or start a new message.</p>
          </div>
        </template>
        <template v-else>
          <div class="border-b border-zinc-200 p-4 dark:border-zinc-800">
            <button
              type="button"
              class="mb-2 text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 lg:hidden"
              @click="selectedId = null"
            >
              ← Back
            </button>
            <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ currentConversation?.other_participant?.name ?? 'Teacher' }}</h3>
            <p v-if="currentConversation?.subject" class="text-sm text-zinc-500 dark:text-zinc-400">{{ currentConversation.subject }}</p>
          </div>
          <div class="max-h-96 overflow-y-auto p-4 space-y-3">
            <div v-if="messagesLoading" class="py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">Loading…</div>
            <template v-else>
              <div
                v-for="m in messages"
                :key="m.id"
                class="flex"
                :class="m.sender_id === currentUserId ? 'justify-end' : 'justify-start'"
              >
                <div
                  class="max-w-[85%] rounded-lg px-4 py-2"
                  :class="m.sender_id === currentUserId ? 'bg-zinc-800 text-white dark:bg-zinc-700' : 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100'"
                >
                  <p class="text-xs font-medium opacity-80">{{ m.sender_name }}</p>
                  <p class="mt-1 whitespace-pre-wrap text-sm">{{ m.body }}</p>
                  <p class="mt-1 text-xs opacity-70">{{ formatDate(m.created_at) }}</p>
                </div>
              </div>
              <button
                v-if="messagesMeta?.current_page < messagesMeta?.last_page"
                type="button"
                class="w-full rounded-lg border border-zinc-300 py-2 text-sm dark:border-zinc-600"
                @click="loadMoreMessages"
              >
                Load older
              </button>
            </template>
          </div>
          <form class="border-t border-zinc-200 p-4 dark:border-zinc-800" @submit.prevent="sendReply">
            <textarea
              v-model="replyBody"
              rows="3"
              class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="Type your message…"
              maxlength="5000"
              required
            />
            <button
              type="submit"
              class="mt-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
              :disabled="sending"
            >
              {{ sending ? 'Sending…' : 'Send' }}
            </button>
            <p v-if="replyError" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ replyError }}</p>
          </form>
        </template>
      </div>
    </div>

    <div v-if="showCompose" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showCompose = false">
      <div class="w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900" @click.stop>
        <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">New message</h3>
        <form @submit.prevent="sendNewMessage">
          <div class="space-y-4">
            <div>
              <label for="compose-recipient" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">To (teacher)</label>
              <select
                id="compose-recipient"
                v-model="compose.recipient_id"
                required
                class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              >
                <option value="">Select teacher</option>
                <option v-for="r in recipients" :key="r.id" :value="r.id">{{ r.name }}</option>
              </select>
            </div>
            <div>
              <label for="compose-subject" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject (optional)</label>
              <input
                id="compose-subject"
                v-model="compose.subject"
                type="text"
                class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                maxlength="255"
              />
            </div>
            <div>
              <label for="compose-body" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Message</label>
              <textarea
                id="compose-body"
                v-model="compose.body"
                required
                rows="4"
                class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                maxlength="5000"
              />
            </div>
            <div class="flex gap-2">
              <button
                type="button"
                class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600"
                @click="showCompose = false"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-100 dark:text-zinc-900"
                :disabled="composeSending"
              >
                {{ composeSending ? 'Sending…' : 'Send' }}
              </button>
            </div>
            <p v-if="composeError" class="text-sm text-red-600 dark:text-red-400">{{ composeError }}</p>
          </div>
        </form>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useAuthStore } from '../stores/authStore.js';
import PageContainer from '../components/PageContainer.vue';
import {
  getRecipients,
  getConversations,
  getConversation,
  getMessages,
  markRead,
  sendMessage,
  reply,
} from '../services/messageService.js';

const authStore = useAuthStore();
const currentUserId = computed(() => authStore.user?.id);

const conversations = ref([]);
const conversationsLoading = ref(true);
const recipients = ref([]);
const selectedId = ref(null);
const currentConversation = ref(null);
const messages = ref([]);
const messagesMeta = ref(null);
const messagesLoading = ref(false);
const replyBody = ref('');
const sending = ref(false);
const replyError = ref('');
const showCompose = ref(false);
const compose = ref({ recipient_id: '', subject: '', body: '' });
const composeSending = ref(false);
const composeError = ref('');

function formatDate(iso) {
  if (!iso) return '—';
  return new Date(iso).toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' });
}

async function loadConversations() {
  conversationsLoading.value = true;
  try {
    const res = await getConversations();
    conversations.value = res?.data ?? [];
  } catch {
    conversations.value = [];
  } finally {
    conversationsLoading.value = false;
  }
}

async function loadRecipients() {
  try {
    recipients.value = await getRecipients();
  } catch {
    recipients.value = [];
  }
}

function selectConversation(id) {
  selectedId.value = id;
  messages.value = [];
  messagesMeta.value = null;
  replyBody.value = '';
  replyError.value = '';
}

async function loadConversationDetail() {
  if (!selectedId.value) return;
  try {
    const c = await getConversation(selectedId.value);
    currentConversation.value = c;
  } catch {
    currentConversation.value = null;
  }
}

async function loadMessages(append = false) {
  if (!selectedId.value) return;
  if (!append) messagesLoading.value = true;
  try {
    const page = append ? (messagesMeta.value?.current_page ?? 1) + 1 : 1;
    const res = await getMessages(selectedId.value, page);
    const list = res?.data ?? [];
    if (append) {
      messages.value = [...list, ...messages.value];
    } else {
      messages.value = list;
    }
    messagesMeta.value = res?.meta ?? null;
    await markRead(selectedId.value);
    await loadConversations();
  } catch {
    if (!append) messages.value = [];
  } finally {
    messagesLoading.value = false;
  }
}

async function loadMoreMessages() {
  loadMessages(true);
}

watch(selectedId, async (id) => {
  if (!id) {
    currentConversation.value = null;
    return;
  }
  await loadConversationDetail();
  await loadMessages();
});

async function sendReply() {
  if (!selectedId.value || !replyBody.value.trim()) return;
  sending.value = true;
  replyError.value = '';
  try {
    await reply(selectedId.value, replyBody.value.trim());
    replyBody.value = '';
    await loadMessages();
    await loadConversations();
  } catch (e) {
    replyError.value = e.response?.data?.message ?? 'Failed to send.';
  } finally {
    sending.value = false;
  }
}

async function sendNewMessage() {
  if (!compose.value.recipient_id || !compose.value.body?.trim()) return;
  composeSending.value = true;
  composeError.value = '';
  try {
    const res = await sendMessage({
      recipient_id: Number(compose.value.recipient_id),
      subject: compose.value.subject?.trim() || undefined,
      body: compose.value.body.trim(),
    });
    showCompose.value = false;
    compose.value = { recipient_id: '', subject: '', body: '' };
    await loadConversations();
    if (res?.conversation?.id) {
      selectedId.value = res.conversation.id;
      await loadConversationDetail();
      await loadMessages();
    }
  } catch (e) {
    if (e.response?.status === 422 && e.response?.data?.conversation_id) {
      selectedId.value = e.response.data.conversation_id;
      showCompose.value = false;
      compose.value = { recipient_id: '', subject: '', body: '' };
      loadConversationDetail();
      loadMessages();
      loadConversations();
    } else {
      composeError.value = e.response?.data?.message ?? 'Failed to send.';
    }
  } finally {
    composeSending.value = false;
  }
}

onMounted(() => {
  loadConversations();
  loadRecipients();
});
</script>
