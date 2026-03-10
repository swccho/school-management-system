import api from './api.js';

export async function getAssignments(params = {}) {
  const { data } = await api.get('/homework', { params });
  return data;
}

export async function getAssignment(id) {
  const { data } = await api.get(`/homework/${id}`);
  return data;
}

export async function submitAssignment(id, formData) {
  const { data } = await api.post(`/homework/${id}/submit`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return data;
}
