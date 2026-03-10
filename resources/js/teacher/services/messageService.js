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
  const { data } = await api.get(`/conversations/${id}`);
  return data;
}

export async function getMessages(conversationId, page = 1) {
  const { data } = await api.get(`/conversations/${conversationId}/messages`, { params: { page } });
  return data;
}

export async function markAsRead(conversationId) {
  await api.post(`/conversations/${conversationId}/mark-read`);
}

export async function sendMessage(payload) {
  const formData = new FormData();
  formData.append('recipient_id', payload.recipient_id);
  if (payload.subject) formData.append('subject', payload.subject);
  formData.append('body', payload.body);
  if (payload.attachments?.length) {
    payload.attachments.forEach((file) => formData.append('attachments[]', file));
  }
  const { data } = await api.post('/messages', formData);
  return data;
}

export async function reply(conversationId, payload) {
  const formData = new FormData();
  formData.append('body', payload.body);
  if (payload.attachments?.length) {
    payload.attachments.forEach((file) => formData.append('attachments[]', file));
  }
  const { data } = await api.post(`/conversations/${conversationId}/messages`, formData);
  return data;
}

export function getAttachmentDownloadUrl(conversationId, messageId, attachmentId) {
  return `/api/teacher/conversations/${conversationId}/messages/${messageId}/attachments/${attachmentId}/download`;
}
