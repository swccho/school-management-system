import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getResults(params = {}) {
  const { data } = await api.get('/admin/results', { params });
  return data;
}

export async function getResultDetail(id) {
  const { data } = await api.get(`/admin/results/${id}`);
  return data;
}

export async function generateResults(payload) {
  const { data } = await api.post('/admin/results/generate', payload);
  return data;
}
