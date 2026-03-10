import api, { getCsrfCookie } from './api.js';

export async function login(credentials) {
  await getCsrfCookie();
  const { data } = await api.post('/login', credentials);
  return data;
}

export async function logout() {
  await api.post('/logout');
}

export async function getMe() {
  const { data } = await api.get('/me');
  return data;
}
