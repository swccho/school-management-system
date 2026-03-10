import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getMaintenanceStatus() {
  const { data } = await api.get('/admin/system-maintenance/status');
  return data;
}

export async function enableMaintenance() {
  const { data } = await api.post('/admin/system-maintenance/enable');
  return data;
}

export async function disableMaintenance() {
  const { data } = await api.post('/admin/system-maintenance/disable');
  return data;
}
