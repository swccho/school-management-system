import api from './api.js';

export async function getAttendanceTrends(params) {
  const { data } = await api.get('/analytics/attendance-trends', { params });
  return data;
}

export async function getHomeworkTrends(params) {
  const { data } = await api.get('/analytics/homework-trends', { params });
  return data;
}

export async function getMarksTrends(params) {
  const { data } = await api.get('/analytics/marks-trends', { params });
  return data;
}

export async function getStudentsNeedingAttention(params) {
  const { data } = await api.get('/analytics/students-needing-attention', { params });
  return data;
}
