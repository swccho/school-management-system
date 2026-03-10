import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

/**
 * @param {Object} params - search, module, action, user_id, created_from, created_to, page, per_page
 * @returns {Promise<{ data: Array, meta: { current_page, last_page, per_page, total, from, to } }>}
 */
export async function getActivityLogs(params = {}) {
  const { data } = await api.get('/admin/activity-logs', { params });
  return data;
}

export async function getActivityLogFilterOptions() {
  const { data } = await api.get('/admin/activity-logs/filter-options');
  return data;
}

export async function getActivityLog(id) {
  const { data } = await api.get(`/admin/activity-logs/${id}`);
  return data;
}
