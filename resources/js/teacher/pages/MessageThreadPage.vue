<template>
  <PageContainer title="Message" description="Conversation thread.">
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="!conversation" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      Conversation not found.
    </div>
    <div v-else class="flex flex-col gap-4">
      <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <div>
          <router-link :to="{ name: 'teacher-messages' }" class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">← Back to Messages</router-link>
          <h2 class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ conversation.other_participant?.name ?? 'Unknown' }}</h2>
          <p v-if="conversation.subject" class="text-sm text-zinc-500">{{ conversation.subject }}</p>
        </div>
      </div>
      <div class="flex flex-1 flex-col gap-3 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <div v-if="messagesLoading" class="py-4 text-center text-sm text-zinc-500">Loading messages…</div>
        <template v-else>
          <div v-for="m in messages" :key="m.id" class="flex" :class="m.sender_id === currentUserId ? 'justify-end' : 'justify-start'">
            <div
              class="max-w-[85%] rounded-lg px-4 py-2"
              :class="m.sender_id === currentUserId ? 'bg-zinc-800 text-white dark:bg-zinc-700' : 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100'"
            >
              <p class="text-xs font-medium opacity-80">{{ m.sender_name }}</p>
              <p class="mt-1 whitespace-pre-wrap text-sm">{{ m.body }}</p>
              <div v-if="m.attachments?.length" class="mt-2 space-y-1">
                <a
                  v-for="a in m.attachments"
                  :key="a.id"
                  :href="attachmentUrl(m.id, a)"
                  target="_blank"
                  rel="noopener"
                  class="block text-xs underline"
                >
                  {{ a.file_name }}
                </a>
              </div>
              <p class="mt-1 text-xs opacity-70">{{ formatDate(m.created_at) }}</p>
            </div>
          </div>
          <button
            v-if="messagesMeta?.current_page < messagesMeta?.last_page"
            type="button"
            class="self-center rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600"
            @click="loadMoreMessages"
          >
            Load older messages
          </button>
        </template>
      </div>
      <form class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900" @submit.prevent="sendReply">
        <textarea
          v-model="replyBody"
          rows="3"
          class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          placeholder="Type your reply…"
          maxlength="5000"
        />
        <div class="mt-2 flex flex-wrap items-center gap-2">
          <input type="file" accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx" multiple class="text-sm" @change="replyAttachments = Array.from($event.target.files || [])" />
          <button type="submit" class="rounded-lg bg-zinc-800 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-700" :disabled="sending">{{ sending ? 'Sending…' : 'Send' }}</button>
        </div>
        <p v-if="replyError" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ replyError }}</p>
      </form>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/authStore.js';
import PageContainer from '../components/PageContainer.vue';
import { getConversation, getMessages, markAsRead, reply, getAttachmentDownloadUrl } from '../services/messageService.js';

const route = useRoute();
const authStore = useAuthStore();
const currentUserId = computed(() => authStore.user?.id);

const conversationId = computed(() => route.params.id);
const loading = ref(true);
const conversation = ref(null);
const messages = ref([]);
const messagesMeta = ref(null);
const messagesLoading = ref(true);
const replyBody = ref('');
const replyAttachments = ref([]);
const sending = ref(false);
const replyError = ref('');

function formatDate(iso) {
  if (!iso) return '—';
  return new Date(iso).toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' });
}

function attachmentUrl(messageId, attachment) {
  const path = getAttachmentDownloadUrl(conversationId.value, messageId, attachment.id);
  return `/api/teacher/${path}`;
}

async function loadConversation() {
  if (!conversationId.value) return;
  try {
    conversation.value = await getConversation(conversationId.value);
    await markAsRead(conversationId.value);
  } catch {
    conversation.value = null;
  } finally {
    loading.value = false;
  }
}

async function loadMessages(page = 1) {
  if (!conversationId.value) return;
  messagesLoading.value = true;
  try {
    const res = await getMessages(conversationId.value, page);
    const newList = res.data ?? [];
    if (page === 1) {
      messages.value = newList.reverse();
    } else {
      messages.value = [...newList.reverse(), ...messages.value];
    }
    messagesMeta.value = res.meta ?? null;
  } catch {
    messages.value = [];
  } finally {
    messagesLoading.value = false;
  }
}

function loadMoreMessages() {
  const next = (messagesMeta.value?.current_page ?? 0) + 1;
  if (next <= (messagesMeta.value?.last_page ?? 0)) {
    loadMessages(next);
  }
}

async function sendReply() {
  if (!replyBody.value.trim()) return;
  replyError.value = '';
  sending.value = true;
  try {
    const res = await reply(conversationId.value, {
      body: replyBody.value.trim(),
      attachments: replyAttachments.value,
    });
    if (res?.data) {
      messages.value.push(res.data);
    }
    replyBody.value = '';
    replyAttachments.value = [];
  } catch (e) {
    replyError.value = e.response?.data?.message ?? 'Failed to send.';
  } finally {
    sending.value = false;
  }
}

onMounted(async () => {
  await loadConversation();
  if (conversation.value) {
    await loadMessages(1);
  } else {
    messagesLoading.value = false;
  }
});
</script>
