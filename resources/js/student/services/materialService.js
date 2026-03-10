import api from './api.js';

export async function getMaterials(params = {}) {
  const { data } = await api.get('/materials', { params });
  return data;
}
