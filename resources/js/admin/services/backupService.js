import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getBackups(params = {}) {
  const { data } = await api.get('/admin/system-backups', { params });
  return data;
}

export async function getBackup(id) {
  const { data } = await api.get(`/admin/system-backups/${id}`);
  return data;
}

export async function generateBackup() {
  const { data } = await api.post('/admin/system-backups/generate');
  return data;
}
