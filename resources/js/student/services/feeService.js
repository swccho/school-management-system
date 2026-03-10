import api from './api.js';

/**
 * @returns {Promise<{ summary: { total_amount: number, paid_amount: number, due_amount: number, status: string|null }, fees: Array, payment_history: Array }>}
 */
export async function getFees() {
  const { data } = await api.get('/fees');
  return data;
}
