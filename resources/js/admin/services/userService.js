import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getUsers(params = {}) {
  const { data } = await api.get('/admin/users', { params });
  return data;
}

export async function getUser(id) {
  const { data } = await api.get(`/admin/users/${id}`);
  return data;
}

export async function createUser(payload) {
  const isFormData = payload instanceof FormData;
  const { data } = await api.post('/admin/users', payload, {
    headers: isFormData ? { 'Content-Type': 'multipart/form-data' } : {},
  });
  return data;
}

export async function updateUser(id, payload) {
  const isFormData = payload instanceof FormData;
  const { data } = await api.put(`/admin/users/${id}`, payload, {
    headers: isFormData ? { 'Content-Type': 'multipart/form-data' } : {},
  });
  return data;
}

export async function deleteUser(id) {
  const { data } = await api.delete(`/admin/users/${id}`);
  return data;
}

export async function activateUser(id) {
  const { data } = await api.post(`/admin/users/${id}/activate`);
  return data;
}

export async function deactivateUser(id) {
  const { data } = await api.post(`/admin/users/${id}/deactivate`);
  return data;
}

export async function resetUserPassword(id, { password, password_confirmation }) {
  const { data } = await api.post(`/admin/users/${id}/reset-password`, {
    password,
    password_confirmation,
  });
  return data;
}

export async function getRolesOptions() {
  const { data } = await api.get('/admin/users/options/roles');
  return data;
}

export async function getLinkableEntitiesOptions(userId = null) {
  const params = userId != null ? { user_id: userId } : {};
  const { data } = await api.get('/admin/users/options/linkable-entities', { params });
  return data;
}
