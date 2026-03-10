import api from './api.js';

export async function getAttendance(params = {}) {
  const { data } = await api.get('/attendance', { params });
  return data;
}
