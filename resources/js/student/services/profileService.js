import api from './api.js';

export async function getProfile() {
  const { data } = await api.get('/profile');
  return data;
}
