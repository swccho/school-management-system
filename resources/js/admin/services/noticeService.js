import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getNotices(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/notices', { params: clean });
  return data;
}

export async function getNotice(id) {
  const { data } = await api.get(`/admin/notices/${id}`);
  return data;
}

function buildNoticeFormData(payload) {
  const form = new FormData();
  if (payload.title != null) form.append('title', payload.title);
  if (payload.content != null) form.append('content', payload.content);
  if (payload.category_id != null) form.append('category_id', payload.category_id);
  if (payload.publish_date != null) form.append('publish_date', payload.publish_date);
  if (payload.expiry_date != null && payload.expiry_date !== '') form.append('expiry_date', payload.expiry_date);
  if (payload.status != null) form.append('status', payload.status);
  form.append('is_featured', payload.is_featured === true ? '1' : '0');
  (payload.remove_attachment_ids || []).forEach((id) => form.append('remove_attachment_ids[]', id));
  (payload.attachments || []).forEach((file) => form.append('attachments[]', file));
  return form;
}

export async function createNotice(payload) {
  const hasFiles = (payload.attachments || []).length > 0;
  if (hasFiles) {
    const form = buildNoticeFormData(payload);
    const { data } = await api.post('/admin/notices', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data;
  }
  const { data } = await api.post('/admin/notices', payload);
  return data;
}

export async function updateNotice(id, payload) {
  const hasFiles = (payload.attachments || []).length > 0;
  const hasRemovals = (payload.remove_attachment_ids || []).length > 0;
  if (hasFiles || hasRemovals) {
    const form = buildNoticeFormData(payload);
    form.append('_method', 'PUT');
    const { data } = await api.post(`/admin/notices/${id}`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data;
  }
  const { data } = await api.put(`/admin/notices/${id}`, payload);
  return data;
}

export async function deleteNotice(id) {
  await api.delete(`/admin/notices/${id}`);
}

export async function publishNotice(id) {
  const { data } = await api.post(`/admin/notices/${id}/publish`);
  return data;
}

export async function unpublishNotice(id) {
  const { data } = await api.post(`/admin/notices/${id}/unpublish`);
  return data;
}
