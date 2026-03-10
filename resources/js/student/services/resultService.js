import api from './api.js';

export async function getResults() {
  const { data } = await api.get('/results');
  return data;
}
