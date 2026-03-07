import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getStaffs(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/staffs', { params: clean });
  return data;
}

export async function getStaff(id) {
  const { data } = await api.get(`/admin/staffs/${id}`);
  return data;
}

export async function getStaffsForTeacher() {
  const { data } = await api.get('/admin/staffs', { params: { for_teacher: 1 } });
  return data;
}

export async function createStaff(payload) {
  const { data } = await api.post('/admin/staffs', payload);
  return data;
}

export async function updateStaff(id, payload) {
  const { data } = await api.put(`/admin/staffs/${id}`, payload);
  return data;
}
