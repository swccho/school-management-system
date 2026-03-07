import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getDepartments() {
  const { data } = await api.get('/admin/staff-departments');
  return data;
}

export async function createDepartment(payload) {
  const { data } = await api.post('/admin/staff-departments', payload);
  return data;
}

export async function updateDepartment(id, payload) {
  const { data } = await api.put(`/admin/staff-departments/${id}`, payload);
  return data;
}
