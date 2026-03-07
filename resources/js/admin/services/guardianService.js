import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getGuardians(params = {}) {
  const { data } = await api.get('/admin/guardians', { params });
  return data;
}

export async function createGuardian(payload) {
  const { data } = await api.post('/admin/guardians', payload);
  return data;
}

export async function updateGuardian(id, payload) {
  const { data } = await api.put(`/admin/guardians/${id}`, payload);
  return data;
}
