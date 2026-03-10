import api from './api.js';

export async function getExams(params) {
  const { data } = await api.get('/exams', { params });
  return data;
}

export async function getMarksEntryContexts(examId) {
  const { data } = await api.get(`/exams/${examId}/marks-entry/contexts`);
  return data;
}

export async function getMarksEntry(examId, params) {
  const { data } = await api.get(`/exams/${examId}/marks-entry`, { params });
  return data;
}

export async function saveMarks(examId, payload) {
  const { data } = await api.put(`/exams/${examId}/marks-entry`, payload);
  return data;
}

export async function submitMarks(examId, payload) {
  const { data } = await api.post(`/exams/${examId}/marks-entry/submit`, payload);
  return data;
}
