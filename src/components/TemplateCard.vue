<script setup>
import { computed } from 'vue';

const props = defineProps({
  template: {
    type: Object,
    required: true
  },
  isPro: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['select', 'preview', 'customize']);

const isSelected = computed(() => props.template.selected);
</script>

<template>
  <div
    class="template-card tw-bg-white tw-rounded-xl tw-border-2 tw-overflow-hidden tw-transition-all tw-duration-200"
    :class="[
      isSelected ? 'tw-border-blue-500 tw-shadow-lg' : 'tw-border-gray-200 hover:tw-border-gray-300'
    ]"
  >
    <!-- Header: Name + Toggle -->
    <div class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-3 tw-border-b tw-border-gray-100 tw-bg-gray-50/50">
      <div class="tw-flex tw-items-center tw-gap-2">
        <h3 class="tw-font-semibold tw-text-gray-900 tw-text-sm tw-m-0">{{ template.name }}</h3>
        <span
          v-if="!template.isPro"
          class="tw-px-1.5 tw-py-0.5 tw-text-[10px] tw-font-bold tw-bg-emerald-100 tw-text-emerald-700 tw-rounded"
        >{{ __('FREE', 'giant-checkout-offers-for-woocommerce') }}</span>
        <span
          v-else
          class="tw-px-1.5 tw-py-0.5 tw-text-[10px] tw-font-bold tw-bg-purple-100 tw-text-purple-700 tw-rounded"
        >{{ __('PRO', 'giant-checkout-offers-for-woocommerce') }}</span>
      </div>

      <!-- Toggle Switch -->
      <div
        @click.stop="emit('select', template)"
        class="toggle-switch tw-relative tw-w-11 tw-h-6 tw-rounded-full tw-transition-all tw-duration-200 tw-flex-shrink-0 tw-cursor-pointer"
        :class="isSelected ? 'active' : 'inactive'"
        role="switch"
        :aria-checked="isSelected"
      >
        <span
          class="toggle-knob tw-absolute tw-top-[3px] tw-w-[18px] tw-h-[18px] tw-bg-white tw-rounded-full tw-transition-all tw-duration-200"
          :style="isSelected ? 'left: calc(100% - 21px)' : 'left: 3px'"
        ></span>
      </div>
    </div>

    <!-- Preview Area -->
    <div
      class="tw-p-4 tw-bg-gray-50 tw-cursor-pointer tw-h-[180px] tw-flex tw-items-center tw-justify-center tw-overflow-hidden"
      @click="emit('preview', template)"
    >
      <slot name="preview">
        <div class="tw-text-gray-400 tw-text-sm">{{ __('Preview', 'giant-checkout-offers-for-woocommerce') }}</div>
      </slot>
    </div>

    <!-- Customize Button -->
    <div class="tw-px-4 tw-py-2.5 tw-border-t tw-border-gray-100 tw-bg-white">
      <button
        @click.stop="emit('customize', template)"
        class="tw-w-full tw-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-3 tw-py-2 tw-text-xs tw-font-medium tw-text-gray-600 tw-bg-gray-50 tw-border tw-border-gray-200 tw-rounded-lg hover:tw-bg-gray-100 hover:tw-text-gray-800 tw-transition-colors"
      >
        <svg class="tw-w-3.5 tw-h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
        </svg>
        {{ __('Customize Style', 'giant-checkout-offers-for-woocommerce') }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.template-card:hover {
  transform: translateY(-1px);
}

.toggle-switch {
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.15);
}

.toggle-switch.active {
  background-color: #3b82f6;
}

.toggle-switch.inactive {
  background-color: #d1d5db;
}

.toggle-knob {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25), 0 1px 2px rgba(0, 0, 0, 0.1);
}
</style>
