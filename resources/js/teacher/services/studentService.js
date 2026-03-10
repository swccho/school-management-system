import api from './api.js';

export async function getStudents(params) {
  const { data } = await api.get('/students', { params });
  return data;
}
