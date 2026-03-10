import api from './api.js';

export async function getRecipients() {
  const { data } = await api.get('/messages/recipients');
  return data;
}

export async function getConversations(params) {
  const { data } = await api.get('/messages', { params });
  return data;
}

export async function getConversation(id) {
  const { data } = await api.get(`/messages/conversations/${id}`);
  return data;
}

export async function getMessages(conversationId, page = 1) {
  const { data } = await api.get(`/messages/conversations/${conversationId}/messages`, { params: { page } });
  return data;
}

export async function markRead(conversationId) {
  await api.post(`/messages/conversations/${conversationId}/mark-read`);
}

export async function sendMessage(payload) {
  const { data } = await api.post('/messages', {
    recipient_id: payload.recipient_id,
    subject: payload.subject || undefined,
    body: payload.body,
  });
  return data;
}

export async function reply(conversationId, body) {
  const { data } = await api.post(`/messages/conversations/${conversationId}/messages`, { body });
  return data;
}
