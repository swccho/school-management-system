import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getAttendanceSessions(params = {}) {
  const { data } = await api.get('/admin/attendance-sessions', { params });
  return data;
}

export async function getAttendanceSession(id) {
  const { data } = await api.get(`/admin/attendance-sessions/${id}`);
  return data;
}

export async function getEligibleStudents(academicSessionId, classId, sectionId) {
  const { data } = await api.get('/admin/attendance-sessions/eligible-students', {
    params: {
      academic_session_id: academicSessionId,
      class_id: classId,
      section_id: sectionId,
    },
  });
  return data;
}

export async function createAttendance(payload) {
  const { data } = await api.post('/admin/attendance-sessions', payload);
  return data;
}

export async function updateAttendance(id, payload) {
  const { data } = await api.put(`/admin/attendance-sessions/${id}`, payload);
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
