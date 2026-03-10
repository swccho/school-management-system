import api from './api.js';

export async function getHomeworkList(params) {
  const { data } = await api.get('/homework', { params });
  return data;
}

export async function getHomework(id) {
  const { data } = await api.get(`/homework/${id}`);
  return data;
}

function toFormData(payload) {
  const fd = new FormData();
  Object.keys(payload).forEach((k) => {
    if (payload[k] != null && k !== 'attachment') fd.append(k, payload[k]);
  });
  if (payload.attachment instanceof File) fd.append('attachment', payload.attachment);
  return fd;
}

export async function createHomework(payload) {
  const { data } = await api.post('/homework', toFormData(payload));
  return data;
}

export async function updateHomework(id, payload) {
  const fd = toFormData(payload);
  fd.append('_method', 'PUT');
  const { data } = await api.post(`/homework/${id}`, fd);
  return data;
}

export async function deleteHomework(id) {
  await api.delete(`/homework/${id}`);
}
