<template>
  <div>
    <router-view />
    <AppToaster />
  </div>
</template>

<script setup>
import AppToaster from '../shared/components/feedback/AppToaster.vue';
</script>
