import { defineStore } from 'pinia';
import { ref } from 'vue';
import * as authService from '../services/authService.js';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const authReady = ref(false);

  async function fetchUser() {
    try {
      const data = await authService.getUser();
      user.value = data;
      return data;
    } catch {
      user.value = null;
      throw new Error('Not authenticated');
    }
  }

  function setUser(userData) {
    user.value = userData;
  }

  function clearUser() {
    user.value = null;
  }

  async function initAuth() {
    if (authReady.value) return;
    try {
      await fetchUser();
    } catch {
      user.value = null;
    } finally {
      authReady.value = true;
    }
  }

  return {
    user,
    authReady,
    setUser,
    clearUser,
    fetchUser,
    initAuth,
  };
});
