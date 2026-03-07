import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getExams(params = {}) {
  const { data } = await api.get('/admin/exams', { params });
  return data;
}

export async function getExam(id) {
  const { data } = await api.get(`/admin/exams/${id}`);
  return data;
}

export async function createExam(payload) {
  const { data } = await api.post('/admin/exams', payload);
  return data;
}

export async function updateExam(id, payload) {
  const { data } = await api.put(`/admin/exams/${id}`, payload);
  return data;
}

export async function getAcademicSessions() {
  const { data } = await api.get('/admin/academic-sessions');
  return data;
}

export async function getClasses() {
  const { data } = await api.get('/admin/classes');
  return data;
}

export async function getSections(classId = null) {
  const params = classId ? { class_id: classId } : {};
  const { data } = await api.get('/admin/sections', { params });
  return data;
}

export async function getSubjects() {
  const { data } = await api.get('/admin/subjects');
  return data;
}

export async function getExamTypes() {
  const { data } = await api.get('/admin/exam-types');
  return data;
}
