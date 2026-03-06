import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useAppStore = defineStore('app', () => {
  const sidebarOpen = ref(true);
  const portalTitle = ref('School Admin');

  const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
  };

  return {
    sidebarOpen,
    portalTitle,
    toggleSidebar,
  };
});
