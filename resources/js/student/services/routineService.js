import api from './api.js';

export async function getRoutine(params = {}) {
  const { data } = await api.get('/routine', { params });
  return data;
}
