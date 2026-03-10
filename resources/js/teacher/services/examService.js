import api from './api.js';

export async function getExams(params) {
  const { data } = await api.get('/exams', { params });
  return data;
}

export async function getExamFilterOptions() {
  const { data } = await api.get('/exams/filter-options');
  return data;
}

export async function getExam(examId) {
  const { data } = await api.get(`/exams/${examId}`);
  return data;
}

export async function getExamSummary(examId) {
  const { data } = await api.get(`/exams/${examId}/summary`);
  return data;
}

export async function getMarksEntryContexts(examId) {
  const { data } = await api.get(`/exams/${examId}/marks-entry/contexts`);
  return data;
}

export async function getMarksEntryContextsWithStatus(examId) {
  const { data } = await api.get(`/exams/${examId}/marks-entry/contexts-with-status`);
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
