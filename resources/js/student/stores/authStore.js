import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import * as authService from '../services/authService.js';

export const useAuthStore = defineStore('studentAuth', () => {
  const user = ref(null);
  const student = ref(null);
  const schoolId = ref(null);
  const currentAcademicSession = ref(null);
  const currentAssignment = ref(null);
  const authReady = ref(false);

  const isAuthenticated = computed(() => !!user.value && !!student.value);
  const studentName = computed(() => student.value?.full_name || user.value?.name || 'Student');

  function setFromMePayload(payload) {
    user.value = payload.user ?? null;
    student.value = payload.student ?? null;
    schoolId.value = payload.school_id ?? null;
    currentAcademicSession.value = payload.current_academic_session ?? null;
    currentAssignment.value = payload.current_assignment ?? null;
  }

  function clear() {
    user.value = null;
    student.value = null;
    schoolId.value = null;
    currentAcademicSession.value = null;
    currentAssignment.value = null;
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
    student,
    schoolId,
    currentAcademicSession,
    currentAssignment,
    authReady,
    isAuthenticated,
    studentName,
    setFromMePayload,
    clear,
    fetchMe,
    initAuth,
  };
});
