import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getSubjects() {
  const { data } = await api.get('/admin/subjects');
  return data;
}

export async function getSubject(id) {
  const { data } = await api.get(`/admin/subjects/${id}`);
  return data;
}

export async function createSubject(payload) {
  const { data } = await api.post('/admin/subjects', payload);
  return data;
}

export async function updateSubject(id, payload) {
  const { data } = await api.put(`/admin/subjects/${id}`, payload);
  return data;
}
