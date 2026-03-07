import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getSchoolProfile() {
  const { data } = await api.get('/admin/school-profile');
  return data;
}

export async function updateSchoolProfile(payload) {
  const { data } = await api.post('/admin/school-profile', payload, {
    headers: payload instanceof FormData ? {} : { 'Content-Type': 'application/json' },
  });
  return data;
}

export async function getSchoolSettings() {
  const { data } = await api.get('/admin/school-settings');
  return data;
}

export async function updateSchoolSettings(payload) {
  const { data } = await api.put('/admin/school-settings', payload);
  return data;
}
