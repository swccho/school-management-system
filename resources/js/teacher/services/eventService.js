import api from './api.js';

export async function getEvents(params) {
  const { data } = await api.get('/events', { params });
  return data;
}
