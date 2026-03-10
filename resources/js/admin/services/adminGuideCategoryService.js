import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getCategories() {
  const { data } = await api.get('/admin/admin-guide-categories');
  return data;
}

export async function getCategory(id) {
  const { data } = await api.get(`/admin/admin-guide-categories/${id}`);
  return data;
}

export async function createCategory(payload) {
  const { data } = await api.post('/admin/admin-guide-categories', payload);
  return data;
}

export async function updateCategory(id, payload) {
  const { data } = await api.put(`/admin/admin-guide-categories/${id}`, payload);
  return data;
}

export async function deleteCategory(id) {
  await api.delete(`/admin/admin-guide-categories/${id}`);
}

export async function reorderCategoryIds(categoryIds) {
  const { data } = await api.post('/admin/admin-guide-categories/reorder', { category_ids: categoryIds });
  return data;
}
