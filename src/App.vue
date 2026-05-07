<!-- src/App.vue -->
<script setup>
import { ref, provide } from 'vue';
import Header from './components/Header/Header.vue';
import BumpModal from './components/BumpModal.vue';
import bumpService from './services/bumpService';
import message from './utils/message';

const showBumpModal = ref(false);
const editingBump = ref(null);

// Provide a reload function so child pages can trigger refresh
const reloadBumpsKey = ref(0);
provide('reloadBumps', () => { reloadBumpsKey.value++; });
provide('reloadBumpsKey', reloadBumpsKey);

const openAddBumpModal = () => {
  editingBump.value = null;
  showBumpModal.value = true;
};

const handleSaveBump = async (bumpData) => {
  try {
    const res = await bumpService.create(bumpData);
    if (res.success) {
      message.success(res.message);
      reloadBumpsKey.value++;
    }
  } catch (err) {
    message.error('Failed to save bump');
  }
};
</script>

<template>
  <div>
    <Header @add-bump="openAddBumpModal" />
    <div class="tw-p-4">
      <router-view :key="reloadBumpsKey" />
    </div>

    <!-- Global Bump Modal -->
    <BumpModal
      v-model:visible="showBumpModal"
      :edit-data="editingBump"
      @save="handleSaveBump"
    />
  </div>
</template>

<style scoped>
/* Optional global styles */
</style>
