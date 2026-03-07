import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getExamTypes(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/exam-types', { params: clean });
  return data;
}

export async function createExamType(payload) {
  const { data } = await api.post('/admin/exam-types', payload);
  return data;
}

export async function updateExamType(id, payload) {
  const { data } = await api.put(`/admin/exam-types/${id}`, payload);
  return data;
}
