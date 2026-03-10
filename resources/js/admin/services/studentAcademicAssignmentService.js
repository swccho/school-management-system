import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getAssignments(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/student-academic-assignments', { params: clean });
  return data;
}

export async function getAssignment(id) {
  const { data } = await api.get(`/admin/student-academic-assignments/${id}`);
  return data;
}

export async function createAssignment(payload) {
  const { data } = await api.post('/admin/student-academic-assignments', payload);
  return data;
}

export async function updateAssignment(id, payload) {
  const { data } = await api.put(`/admin/student-academic-assignments/${id}`, payload);
  return data;
}
