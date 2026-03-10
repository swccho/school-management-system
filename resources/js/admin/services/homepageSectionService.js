import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getList() {
  const { data } = await api.get('/admin/homepage-sections');
  return data;
}

export async function getOne(id) {
  const { data } = await api.get(`/admin/homepage-sections/${id}`);
  return data;
}

export async function create(payload) {
  const { data } = await api.post('/admin/homepage-sections', payload);
  return data;
}

export async function update(id, payload) {
  const { data } = await api.put(`/admin/homepage-sections/${id}`, payload);
  return data;
}

export async function deleteSection(id) {
  await api.delete(`/admin/homepage-sections/${id}`);
}

export async function reorder(sectionIds) {
  const { data } = await api.post('/admin/homepage-sections/reorder', { section_ids: sectionIds });
  return data;
}

export async function toggleVisibility(id) {
  const { data } = await api.post(`/admin/homepage-sections/${id}/toggle-visibility`);
  return data;
}
