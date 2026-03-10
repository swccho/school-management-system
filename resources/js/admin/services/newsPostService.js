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
  const { data } = await api.get('/admin/news-posts', { params: clean });
  return data;
}

export async function getOne(id) {
  const { data } = await api.get(`/admin/news-posts/${id}`);
  return data;
}

function buildFormData(payload) {
  const form = new FormData();
  if (payload.title != null) form.append('title', payload.title);
  if (payload.content != null) form.append('content', payload.content);
  if (payload.status != null) form.append('status', payload.status);
  if (payload.category_id != null) form.append('category_id', payload.category_id);
  if (payload.summary != null && payload.summary !== '') form.append('summary', payload.summary);
  if (payload.published_at != null && payload.published_at !== '') form.append('published_at', payload.published_at);
  form.append('is_featured', payload.is_featured === true ? '1' : '0');
  if (payload.featured_image && payload.featured_image instanceof File) {
    form.append('featured_image', payload.featured_image);
  }
  return form;
}

export async function create(payload) {
  const hasFile = payload.featured_image && payload.featured_image instanceof File;
  if (hasFile) {
    const form = buildFormData(payload);
    const { data } = await api.post('/admin/news-posts', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data;
  }
  const { data } = await api.post('/admin/news-posts', payload);
  return data;
}

export async function update(id, payload) {
  const hasFile = payload.featured_image && payload.featured_image instanceof File;
  if (hasFile) {
    const form = buildFormData(payload);
    form.append('_method', 'PUT');
    const { data } = await api.post(`/admin/news-posts/${id}`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data;
  }
  const { data } = await api.put(`/admin/news-posts/${id}`, payload);
  return data;
}

export async function deletePost(id) {
  await api.delete(`/admin/news-posts/${id}`);
}

export async function publish(id) {
  const { data } = await api.post(`/admin/news-posts/${id}/publish`);
  return data;
}

export async function unpublish(id) {
  const { data } = await api.post(`/admin/news-posts/${id}/unpublish`);
  return data;
}
