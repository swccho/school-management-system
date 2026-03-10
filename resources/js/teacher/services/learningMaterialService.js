import api from './api.js';

export async function getLearningMaterials(params) {
  const { data } = await api.get('/learning-materials', { params });
  return data;
}

export async function getLearningMaterialAssignmentOptions() {
  const { data } = await api.get('/learning-materials/assignment-options');
  return data;
}

export async function getLearningMaterial(id) {
  const { data } = await api.get(`/learning-materials/${id}`);
  return data;
}

export async function createLearningMaterial(payload) {
  const fd = payload instanceof FormData ? payload : (() => {
    const fd = new FormData();
    Object.keys(payload).forEach((k) => { if (payload[k] != null && k !== 'file') fd.append(k, payload[k]); });
    if (payload.file instanceof File) fd.append('file', payload.file);
    return fd;
  })();
  const { data } = await api.post('/learning-materials', fd);
  return data;
}

export async function updateLearningMaterial(id, payload) {
  const fd = payload instanceof FormData ? payload : (() => {
    const fd = new FormData();
    fd.append('_method', 'PUT');
    Object.keys(payload).forEach((k) => { if (payload[k] != null && k !== 'file') fd.append(k, payload[k]); });
    if (payload.file instanceof File) fd.append('file', payload.file);
    return fd;
  })();
  const { data } = await api.post(`/learning-materials/${id}`, fd);
  return data;
}

export async function deleteLearningMaterial(id) {
  await api.delete(`/learning-materials/${id}`);
}
