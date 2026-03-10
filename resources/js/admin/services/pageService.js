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
  const { data } = await api.get('/admin/pages', { params: clean });
  return data;
}

export async function getOne(id) {
  const { data } = await api.get(`/admin/pages/${id}`);
  return data;
}

function buildFormData(payload) {
  const form = new FormData();
  if (payload.title != null) form.append('title', payload.title);
  if (payload.slug != null) form.append('slug', payload.slug);
  if (payload.content != null) form.append('content', payload.content);
  if (payload.status != null) form.append('status', payload.status);
  if (payload.page_type != null && payload.page_type !== '') form.append('page_type', payload.page_type);
  if (payload.meta_title != null && payload.meta_title !== '') form.append('meta_title', payload.meta_title);
  if (payload.meta_description != null && payload.meta_description !== '') form.append('meta_description', payload.meta_description);
  if (payload.published_at != null && payload.published_at !== '') form.append('published_at', payload.published_at);
  if (payload.featured_image && payload.featured_image instanceof File) {
    form.append('featured_image', payload.featured_image);
  }
  return form;
}

export async function create(payload) {
  const hasFile = payload.featured_image && payload.featured_image instanceof File;
  if (hasFile) {
    const form = buildFormData(payload);
    const { data } = await api.post('/admin/pages', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data;
  }
  const { data } = await api.post('/admin/pages', payload);
  return data;
}

export async function update(id, payload) {
  const hasFile = payload.featured_image && payload.featured_image instanceof File;
  if (hasFile) {
    const form = buildFormData(payload);
    form.append('_method', 'PUT');
    const { data } = await api.post(`/admin/pages/${id}`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data;
  }
  const { data } = await api.put(`/admin/pages/${id}`, payload);
  return data;
}

export async function deletePage(id) {
  await api.delete(`/admin/pages/${id}`);
}

export async function publish(id) {
  const { data } = await api.post(`/admin/pages/${id}/publish`);
  return data;
}

export async function unpublish(id) {
  const { data } = await api.post(`/admin/pages/${id}/unpublish`);
  return data;
}
