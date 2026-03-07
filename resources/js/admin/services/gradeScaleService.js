import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getGradeScales(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/grade-scales', { params: clean });
  return data;
}

export async function getGradeScale(id) {
  const { data } = await api.get(`/admin/grade-scales/${id}`);
  return data;
}

export async function createGradeScale(payload) {
  const { data } = await api.post('/admin/grade-scales', payload);
  return data;
}

export async function updateGradeScale(id, payload) {
  const { data } = await api.put(`/admin/grade-scales/${id}`, payload);
  return data;
}
