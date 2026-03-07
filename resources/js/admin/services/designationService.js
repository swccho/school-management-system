import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getDesignations(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/designations', { params: clean });
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
