import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getList() {
  const { data } = await api.get('/admin/banners');
  return data;
}

export async function getOne(id) {
  const { data } = await api.get(`/admin/banners/${id}`);
  return data;
}

function buildFormData(payload) {
  const form = new FormData();
  if (payload.title != null) form.append('title', payload.title);
  if (payload.status != null) form.append('status', payload.status);
  if (payload.subtitle != null && payload.subtitle !== '') form.append('subtitle', payload.subtitle);
  if (payload.button_text != null && payload.button_text !== '') form.append('button_text', payload.button_text);
  if (payload.button_url != null && payload.button_url !== '') form.append('button_url', payload.button_url);
  if (payload.sort_order != null) form.append('sort_order', payload.sort_order);
  if (payload.image && payload.image instanceof File) form.append('image', payload.image);
  return form;
}

export async function create(payload) {
  const form = buildFormData(payload);
  const { data } = await api.post('/admin/banners', form, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return data;
}

export async function update(id, payload) {
  const hasFile = payload.image && payload.image instanceof File;
  if (hasFile) {
    const form = buildFormData(payload);
    form.append('_method', 'PUT');
    const { data } = await api.post(`/admin/banners/${id}`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data;
  }
  const clean = { ...payload };
  delete clean.image;
  const { data } = await api.put(`/admin/banners/${id}`, clean);
  return data;
}

export async function deleteBanner(id) {
  await api.delete(`/admin/banners/${id}`);
}

export async function reorder(bannerIds) {
  const { data } = await api.post('/admin/banners/reorder', { banner_ids: bannerIds });
  return data;
}
