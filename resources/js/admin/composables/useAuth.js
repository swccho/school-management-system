import { ref } from 'vue';
import { useAuthStore } from '../stores/authStore.js';
import * as authService from '../services/authService.js';

export function useAuth() {
  const authStore = useAuthStore();
  const loading = ref(false);
  const error = ref(null);

  async function login(credentials) {
    loading.value = true;
    error.value = null;
    try {
      await authService.getCsrfCookie();
      const data = await authService.login(credentials);
      authStore.setUser(data.user);
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
      authStore.clearUser();
    } catch (err) {
      authStore.clearUser();
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchUser() {
    return authStore.fetchUser();
  }

  return {
    user: authStore.user,
    loading,
    error,
    login,
    logout,
    fetchUser,
  };
}
