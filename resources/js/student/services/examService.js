import api from './api.js';

export async function getExams() {
  const { data } = await api.get('/exams');
  return data;
}
