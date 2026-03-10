import { ref } from 'vue';
import { useAuthStore } from '../stores/authStore.js';
import * as authService from '../services/authService.js';

export function useTeacherAuth() {
  const authStore = useAuthStore();
  const loading = ref(false);
  const error = ref(null);

  async function login(credentials) {
    loading.value = true;
    error.value = null;
    try {
      const data = await authService.login(credentials);
      authStore.setFromMePayload(data);
      return data;
    } catch (err) {
      const message = err.response?.data?.message ?? 'Login failed.';
      error.value = message;
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function logout() {
    loading.value = true;
    error.value = null;
    try {
      await authService.logout();
      authStore.clear();
    } catch {
      authStore.clear();
      throw new Error('Logout failed');
    } finally {
      loading.value = false;
    }
  }

  return {
    loading,
    error,
    login,
    logout,
  };
}
