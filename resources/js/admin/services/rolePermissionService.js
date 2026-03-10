import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getRoles(params = {}) {
  const { data } = await api.get('/admin/roles', { params });
  return data;
}

export async function getRole(id) {
  const { data } = await api.get(`/admin/roles/${id}`);
  return data;
}

export async function createRole(payload) {
  const { data } = await api.post('/admin/roles', payload);
  return data;
}

export async function updateRole(id, payload) {
  const { data } = await api.put(`/admin/roles/${id}`, payload);
  return data;
}

export async function deleteRole(id) {
  const { data } = await api.delete(`/admin/roles/${id}`);
  return data;
}

export async function getRolePermissions(roleId) {
  const { data } = await api.get(`/admin/roles/${roleId}/permissions`);
  return data;
}

export async function syncRolePermissions(roleId, permissionIds) {
  const { data } = await api.post(`/admin/roles/${roleId}/permissions`, { permission_ids: permissionIds });
  return data;
}

export async function getPermissions(params = {}) {
  const { data } = await api.get('/admin/permissions', { params });
  return data;
}

export async function getPermission(id) {
  const { data } = await api.get(`/admin/permissions/${id}`);
  return data;
}

export async function createPermission(payload) {
  const { data } = await api.post('/admin/permissions', payload);
  return data;
}

export async function updatePermission(id, payload) {
  const { data } = await api.put(`/admin/permissions/${id}`, payload);
  return data;
}

export async function deletePermission(id) {
  const { data } = await api.delete(`/admin/permissions/${id}`);
  return data;
}
