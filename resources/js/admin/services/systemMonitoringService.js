import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getMonitoringOverview() {
  const { data } = await api.get('/admin/system-monitoring/overview');
  return data;
}

export async function getLoginHistory(params = {}) {
  const { data } = await api.get('/admin/system-monitoring/login-history', { params });
  return data;
}

export async function getActiveSessions(params = {}) {
  const { data } = await api.get('/admin/system-monitoring/active-sessions', { params });
  return data;
}
