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
  const { data } = await api.get('/admin/galleries', { params: clean });
  return data;
}

export async function getOne(id) {
  const { data } = await api.get(`/admin/galleries/${id}`);
  return data;
}

function buildFormData(payload) {
  const form = new FormData();
  if (payload.title != null) form.append('title', payload.title);
  if (payload.status != null) form.append('status', payload.status);
  if (payload.description != null && payload.description !== '') form.append('description', payload.description);
  if (payload.gallery_type != null && payload.gallery_type !== '') form.append('gallery_type', payload.gallery_type);
  if (payload.published_at != null && payload.published_at !== '') form.append('published_at', payload.published_at);
  if (payload.cover_image && payload.cover_image instanceof File) {
    form.append('cover_image', payload.cover_image);
  }
  return form;
}

export async function create(payload) {
  const hasFile = payload.cover_image && payload.cover_image instanceof File;
  if (hasFile) {
    const form = buildFormData(payload);
    const { data } = await api.post('/admin/galleries', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data;
  }
  const { data } = await api.post('/admin/galleries', payload);
  return data;
}

export async function update(id, payload) {
  const hasFile = payload.cover_image && payload.cover_image instanceof File;
  if (hasFile) {
    const form = buildFormData(payload);
    form.append('_method', 'PUT');
    const { data } = await api.post(`/admin/galleries/${id}`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data;
  }
  const { data } = await api.put(`/admin/galleries/${id}`, payload);
  return data;
}

export async function deleteGallery(id) {
  await api.delete(`/admin/galleries/${id}`);
}

export async function publish(id) {
  const { data } = await api.post(`/admin/galleries/${id}/publish`);
  return data;
}

export async function unpublish(id) {
  const { data } = await api.post(`/admin/galleries/${id}/unpublish`);
  return data;
}

export async function addItem(galleryId, payload) {
  const form = new FormData();
  form.append('file', payload.file);
  if (payload.caption != null) form.append('caption', payload.caption);
  if (payload.sort_order != null) form.append('sort_order', payload.sort_order);
  if (payload.status != null) form.append('status', payload.status);
  const { data } = await api.post(`/admin/galleries/${galleryId}/items`, form, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return data;
}

export async function updateItem(itemId, payload) {
  const { data } = await api.put(`/admin/gallery-items/${itemId}`, payload);
  return data;
}

export async function deleteItem(itemId) {
  await api.delete(`/admin/gallery-items/${itemId}`);
}

export async function reorderItems(itemIds) {
  const { data } = await api.post('/admin/gallery-items/reorder', { item_ids: itemIds });
  return data;
}
