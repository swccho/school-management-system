import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export async function getHelpCategories() {
  const { data } = await api.get('/admin/help/categories');
  return data;
}

export async function getHelpGuides(params = {}) {
  const clean = Object.fromEntries(
    Object.entries(params).filter(([, v]) => v != null && v !== '')
  );
  const { data } = await api.get('/admin/help/guides', { params: clean });
  return data;
}

export async function getHelpGuideBySlug(slug) {
  const { data } = await api.get(`/admin/help/guides/${encodeURIComponent(slug)}`);
  return data;
}

export async function searchHelp(q) {
  const { data } = await api.get('/admin/help/search', { params: { q: q || '' } });
  return data;
}
