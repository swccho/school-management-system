import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getClasses() {
  const { data } = await api.get('/admin/classes');
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
