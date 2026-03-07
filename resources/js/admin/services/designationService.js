import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getDesignations() {
  const { data } = await api.get('/admin/designations');
  return data;
}

export async function createDesignation(payload) {
  const { data } = await api.post('/admin/designations', payload);
  return data;
}

export async function updateDesignation(id, payload) {
  const { data } = await api.put(`/admin/designations/${id}`, payload);
  return data;
}
