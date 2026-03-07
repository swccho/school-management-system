import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getSections() {
  const { data } = await api.get('/admin/sections');
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
