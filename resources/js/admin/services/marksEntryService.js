import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getMarksEntryList(params = {}) {
  const { data } = await api.get('/admin/marks-entry', { params });
  return data;
}

export async function getMarksEntryDetail(params) {
  const { data } = await api.get('/admin/marks-entry/detail', { params });
  return data;
}

export async function getEligibleStudents(examId, classId, sectionId = null) {
  const params = { exam_id: examId, class_id: classId };
  if (sectionId) params.section_id = sectionId;
  const { data } = await api.get('/admin/marks-entry/eligible-students', { params });
  return data;
}

export async function getSubjectsForContext(examId, classId) {
  const { data } = await api.get('/admin/marks-entry/subjects-for-context', {
    params: { exam_id: examId, class_id: classId },
  });
  return data;
}

export async function getSubjectConfig(examId, classId, subjectId) {
  const { data } = await api.get('/admin/marks-entry/subject-config', {
    params: { exam_id: examId, class_id: classId, subject_id: subjectId },
  });
  return data;
}

export async function saveMarks(payload) {
  const { data } = await api.post('/admin/marks-entry', payload);
  return data;
}

export async function getExams(params = {}) {
  const { data } = await api.get('/admin/exams', { params });
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
