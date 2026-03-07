import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getClasses(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/classes', { params: clean });
  return data;
}

export async function getClass(id) {
  const { data } = await api.get(`/admin/classes/${id}`);
  return data;
}

export async function createClass(payload) {
  const { data } = await api.post('/admin/classes', payload);
  return data;
}

export async function updateClass(id, payload) {
  const { data } = await api.put(`/admin/classes/${id}`, payload);
  return data;
}

export async function generateCode(payload = {}) {
  const { data } = await api.post('/admin/classes/generate-code', payload);
  return data;
}
