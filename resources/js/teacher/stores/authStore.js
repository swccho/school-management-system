import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import * as authService from '../services/authService.js';

export const useAuthStore = defineStore('teacherAuth', () => {
  const user = ref(null);
  const staff = ref(null);
  const teacher = ref(null);
  const assignments = ref([]);
  const currentAcademicSession = ref(null);
  const authReady = ref(false);

  const isAuthenticated = computed(() => !!user.value && !!teacher.value);
  const teacherName = computed(() => staff.value?.full_name || user.value?.name || 'Teacher');

  function setFromMePayload(payload) {
    user.value = payload.user ?? null;
    staff.value = payload.staff ?? null;
    teacher.value = payload.teacher ?? null;
    assignments.value = payload.assignments ?? [];
    currentAcademicSession.value = payload.current_academic_session ?? null;
  }

  function clear() {
    user.value = null;
    staff.value = null;
    teacher.value = null;
    assignments.value = [];
    currentAcademicSession.value = null;
  }

  async function fetchMe() {
    const data = await authService.getMe();
    setFromMePayload(data);
    return data;
  }

  async function initAuth() {
    if (authReady.value) return;
    try {
      await fetchMe();
    } catch {
      clear();
    } finally {
      authReady.value = true;
    }
  }

  return {
    user,
    staff,
    teacher,
    assignments,
    currentAcademicSession,
    authReady,
    isAuthenticated,
    teacherName,
    setFromMePayload,
    clear,
    fetchMe,
    initAuth,
  };
});
