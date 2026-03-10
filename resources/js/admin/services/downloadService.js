import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getList(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/downloads', { params: clean });
  return data;
}

export async function getOne(id) {
  const { data } = await api.get(`/admin/downloads/${id}`);
  return data;
}

function buildFormData(payload) {
  const form = new FormData();
  if (payload.title != null) form.append('title', payload.title);
  if (payload.status != null) form.append('status', payload.status);
  if (payload.category_id != null) form.append('category_id', payload.category_id);
  if (payload.description != null && payload.description !== '') form.append('description', payload.description);
  if (payload.access_type != null) form.append('access_type', payload.access_type);
  if (payload.published_at != null && payload.published_at !== '') form.append('published_at', payload.published_at);
  if (payload.file && payload.file instanceof File) form.append('file', payload.file);
  return form;
}

export async function create(payload) {
  const form = buildFormData(payload);
  const { data } = await api.post('/admin/downloads', form, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return data;
}

export async function update(id, payload) {
  const hasFile = payload.file && payload.file instanceof File;
  if (hasFile) {
    const form = buildFormData(payload);
    form.append('_method', 'PUT');
    const { data } = await api.post(`/admin/downloads/${id}`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data;
  }
  const clean = { ...payload };
  delete clean.file;
  const { data } = await api.put(`/admin/downloads/${id}`, clean);
  return data;
}

export async function deleteDownload(id) {
  await api.delete(`/admin/downloads/${id}`);
}

export async function publish(id) {
  const { data } = await api.post(`/admin/downloads/${id}/publish`);
  return data;
}

export async function unpublish(id) {
  const { data } = await api.post(`/admin/downloads/${id}/unpublish`);
  return data;
}
