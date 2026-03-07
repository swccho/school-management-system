import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getSessions(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/academic-sessions', { params: clean });
  return data;
}

export async function getSession(id) {
  const { data } = await api.get(`/admin/academic-sessions/${id}`);
  return data;
}

export async function createSession(payload) {
  const { data } = await api.post('/admin/academic-sessions', payload);
  return data;
}

export async function updateSession(id, payload) {
  const { data } = await api.put(`/admin/academic-sessions/${id}`, payload);
  return data;
}

export async function setCurrentSession(id) {
  const { data } = await api.post(`/admin/academic-sessions/${id}/set-current`);
  return data;
}

export async function generateCode(payload = {}) {
  const { data } = await api.post('/admin/academic-sessions/generate-code', payload);
  return data;
}
