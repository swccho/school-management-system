import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getRoles() {
  const { data } = await api.get('/admin/roles');
  return data;
}

export async function getRole(id) {
  const { data } = await api.get(`/admin/roles/${id}`);
  return data;
}

export async function getPermissions() {
  const { data } = await api.get('/admin/permissions');
  return data;
}
