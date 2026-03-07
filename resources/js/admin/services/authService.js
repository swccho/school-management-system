import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getCsrfCookie() {
  await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
}

export async function login(credentials) {
  const { data } = await api.post('/admin/login', credentials);
  return data;
}

export async function logout() {
  await api.post('/admin/logout');
}

export async function getUser() {
  const { data } = await api.get('/admin/me');
  return data;
}

/** Alias for spec compatibility. */
export const getCurrentUser = getUser;
