import api from './api.js';

export async function getLessonPlans(params) {
  const { data } = await api.get('/lesson-plans', { params });
  return data;
}

export async function getLessonPlan(id) {
  const { data } = await api.get(`/lesson-plans/${id}`);
  return data;
}

export async function createLessonPlan(payload) {
  const { data } = await api.post('/lesson-plans', payload);
  return data;
}

export async function updateLessonPlan(id, payload) {
  const { data } = await api.put(`/lesson-plans/${id}`, payload);
  return data;
}

export async function deleteLessonPlan(id) {
  await api.delete(`/lesson-plans/${id}`);
}
