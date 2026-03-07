import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getSessions() {
  const { data } = await api.get('/admin/academic-sessions');
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
