import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getTeachers() {
  const { data } = await api.get('/admin/teachers');
  return data;
}

export async function getTeacher(id) {
  const { data } = await api.get(`/admin/teachers/${id}`);
  return data;
}

export async function createTeacher(payload) {
  const { data } = await api.post('/admin/teachers', payload);
  return data;
}

export async function updateTeacher(id, payload) {
  const { data } = await api.put(`/admin/teachers/${id}`, payload);
  return data;
}
