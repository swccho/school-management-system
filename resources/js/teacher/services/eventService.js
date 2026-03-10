import api from './api.js';

export async function getEvents(params) {
  const { data } = await api.get('/events', { params });
  return data;
}

export async function getEventCategoryOptions() {
  const { data } = await api.get('/events/category-options');
  return data;
}

export async function getEvent(id) {
  const { data } = await api.get(`/events/${id}`);
  return data;
}
