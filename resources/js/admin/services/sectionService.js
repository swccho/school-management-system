import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getSections(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/sections', { params: clean });
  return data;
}

export async function generateCode(payload = {}) {
  const { data } = await api.post('/admin/sections/generate-code', payload);
  return data;
}

export async function getSection(id) {
  const { data } = await api.get(`/admin/sections/${id}`);
  return data;
}

export async function createSection(payload) {
  const { data } = await api.post('/admin/sections', payload);
  return data;
}

export async function updateSection(id, payload) {
  const { data } = await api.put(`/admin/sections/${id}`, payload);
  return data;
}
