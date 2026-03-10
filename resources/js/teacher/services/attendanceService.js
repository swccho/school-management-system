import api from './api.js';

export async function getAttendanceSessions(params = {}) {
  const { data } = await api.get('/attendance', { params });
  return data;
}

export async function createAttendanceSession(payload) {
  const { data } = await api.post('/attendance', payload);
  return data;
}

export async function getAttendanceSession(id) {
  const { data } = await api.get(`/attendance/${id}`);
  return data;
}

export async function updateAttendanceSession(id, payload) {
  await api.put(`/attendance/${id}`, payload);
}

export async function submitAttendanceSession(id) {
  await api.post(`/attendance/${id}/submit`);
}
