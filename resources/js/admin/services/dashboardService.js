import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getDashboardData() {
  const { data } = await api.get('/admin/dashboard');
  return data;
}
