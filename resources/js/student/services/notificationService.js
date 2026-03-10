import api from './api.js';

export async function getNotifications(params) {
  const { data } = await api.get('/notifications', { params });
  return data;
}

export async function getUnreadCount() {
  const { data } = await api.get('/notifications/unread-count');
  return data;
}

export async function markAsRead(id) {
  await api.patch(`/notifications/${id}/read`);
}

export async function markAllAsRead() {
  await api.post('/notifications/mark-all-read');
}
