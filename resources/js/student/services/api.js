import axios from 'axios';

const api = axios.create({
  baseURL: '/api/student',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

export default api;

export async function getCsrfCookie() {
  await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
}
