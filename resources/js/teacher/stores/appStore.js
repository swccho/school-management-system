import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAppStore = defineStore('teacherApp', () => {
  const sidebarOpen = ref(true);
  const portalTitle = 'Teachers Portal';

  function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
  }

  return { sidebarOpen, portalTitle, toggleSidebar };
});
