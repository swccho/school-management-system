import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getClassRoutines(params = {}) {
  const { data } = await api.get('/admin/class-routines', { params });
  return data;
}

export async function getClassRoutine(id) {
  const { data } = await api.get(`/admin/class-routines/${id}`);
  return data;
}

export async function createClassRoutine(payload) {
  const { data } = await api.post('/admin/class-routines', payload);
  return data;
}

export async function updateClassRoutine(id, payload) {
  const { data } = await api.put(`/admin/class-routines/${id}`, payload);
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

export async function getTeachers() {
  const { data } = await api.get('/admin/teachers');
  return data;
}

export const DAYS_OF_WEEK = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

export function dayLabel(day) {
  return day ? day.charAt(0).toUpperCase() + day.slice(1) : '';
}
