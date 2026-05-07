<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

const { __ } = wp?.i18n || { __: (t) => t };

const navItems = [
  {
    name: __('Bumps', 'giant-checkout-offers-for-woocommerce'),
    path: '/',
    icon: 'M13 10V3L4 14h7v7l9-11h-7z',
    isPro: false,
  },
  {
    name: __('Templates', 'giant-checkout-offers-for-woocommerce'),
    path: '/templates',
    icon: 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
    isPro: false,
  },
  {
    name: __('AI Engine', 'giant-checkout-offers-for-woocommerce'),
    path: '/ai-engine',
    icon: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
    isPro: true,
  },
  {
    name: __('Analytics', 'giant-checkout-offers-for-woocommerce'),
    path: '/analytics',
    icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    isPro: false,
  },
  {
    name: __('Settings', 'giant-checkout-offers-for-woocommerce'),
    path: '/settings',
    icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    isPro: false,
  },
];

const isActive = (path) => {
  return route.path === path;
};
</script>

<template>
  <nav class="tw-px-6 tw-flex tw-space-x-1 tw-border-t tw-border-gray-100">
    <router-link
      v-for="item in navItems"
      :key="item.path"
      :to="item.path"
      class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-border-b-2 tw-transition-all tw-flex tw-items-center tw-gap-2 tw-relative"
      :class="isActive(item.path)
        ? 'tw-border-blue-500 tw-text-blue-600 tw-bg-blue-50/50'
        : 'tw-border-transparent tw-text-gray-500 hover:tw-text-gray-700 hover:tw-bg-gray-50'"
    >
      <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
      </svg>
      {{ item.name }}
      <span
        v-if="item.isPro"
        class="tw-ml-1 tw-px-1.5 tw-py-0.5 tw-bg-gradient-to-r tw-from-yellow-400 tw-to-orange-500 tw-text-white tw-text-[9px] tw-font-bold tw-rounded tw-uppercase tw-leading-tight tw-shadow-sm"
      >{{ __('PRO', 'giant-checkout-offers-for-woocommerce') }}</span>
    </router-link>
  </nav>
</template>
