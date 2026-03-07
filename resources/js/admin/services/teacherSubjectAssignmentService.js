import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getAssignments(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/teacher-subject-assignments', { params: clean });
  return data;
}

export async function getAssignment(id) {
  const { data } = await api.get(`/admin/teacher-subject-assignments/${id}`);
  return data;
}

export async function createAssignment(payload) {
  const { data } = await api.post('/admin/teacher-subject-assignments', payload);
  return data;
}

export async function updateAssignment(id, payload) {
  const { data } = await api.put(`/admin/teacher-subject-assignments/${id}`, payload);
  return data;
}

export async function getAcademicSessions() {
  const { data } = await api.get('/admin/academic-sessions');
  return data;
}

export async function getTeachers() {
  const { data } = await api.get('/admin/teachers');
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
