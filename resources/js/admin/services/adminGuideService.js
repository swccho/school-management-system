import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getGuides(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/admin-guides', { params: clean });
  return data;
}

export async function getGuide(id) {
  const { data } = await api.get(`/admin/admin-guides/${id}`);
  return data;
}

export async function createGuide(payload) {
  const { data } = await api.post('/admin/admin-guides', payload);
  return data;
}

export async function updateGuide(id, payload) {
  const { data } = await api.put(`/admin/admin-guides/${id}`, payload);
  return data;
}

export async function deleteGuide(id) {
  await api.delete(`/admin/admin-guides/${id}`);
}
