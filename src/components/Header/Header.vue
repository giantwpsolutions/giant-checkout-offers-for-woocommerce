<script setup>
import Navigation from './Navigation.vue';

const emit = defineEmits(['add-bump']);

const pluginUrl = window.gcowPluginData?.pluginUrl || '';
</script>

<template>
  <div class="tw-bg-white tw-shadow-sm tw-border-b tw-border-gray-200">
    <!-- Top Bar -->
    <div class="tw-px-6 tw-py-4 tw-flex tw-items-center tw-justify-between">
      <div class="tw-flex tw-items-center tw-gap-3">
        <img :src="pluginUrl + 'assets/logo.png'" alt="Smart Order Bump" class="tw-h-10 tw-w-auto" />
      </div>
      <div class="tw-flex tw-items-center tw-gap-3">
        <button
          @click="emit('add-bump')"
          class="header-btn tw-flex tw-items-center tw-gap-1.5 tw-px-5 tw-py-2.5 tw-rounded-lg tw-text-sm tw-font-semibold tw-text-white tw-transition-colors tw-border-none tw-cursor-pointer"
        >
          <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ __('Add New Bump', 'giant-checkout-offers-for-woocommerce') }}
        </button>
      </div>
    </div>
    <!-- Navigation -->
    <Navigation />
  </div>
</template>

<style scoped>
.header-btn {
  background-color: #4056E0;
}
.header-btn:hover {
  background-color: #3448C4;
}
</style>
