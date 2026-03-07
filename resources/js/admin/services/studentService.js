import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getStudents(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/students', { params: clean });
  return data;
}

export async function getStudent(id) {
  const { data } = await api.get(`/admin/students/${id}`);
  return data;
}

export async function createStudent(payload) {
  const { data } = await api.post('/admin/students', payload);
  return data;
}

export async function updateStudent(id, payload) {
  const { data } = await api.put(`/admin/students/${id}`, payload);
  return data;
}

export async function attachGuardian(studentId, payload) {
  const { data } = await api.post(`/admin/students/${studentId}/guardians`, payload);
  return data;
}
