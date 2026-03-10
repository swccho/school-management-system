import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAppStore = defineStore('studentApp', () => {
  const sidebarOpen = ref(true);
  const portalTitle = 'Student Portal';

  function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
  }

  return { sidebarOpen, portalTitle, toggleSidebar };
});
