<script setup>
import { ref, onMounted, computed } from 'vue';
import aiEngineService from '../services/aiEngineService';
import message from '../utils/message';

const { __ } = wp?.i18n || { __: (t) => t };

// ─── State ────────────────────────────────────────────────────────────────────
const status   = ref(null);
const loading  = ref(false);
const learning = ref(false);
const toggling = ref(false);

// ─── Computed ─────────────────────────────────────────────────────────────────
const proActive  = computed(() => !!window.gcowPluginData?.proActive);
const aiEnabled  = computed(() => status.value?.enabled ?? false);
const knowledge  = computed(() => status.value?.knowledge ?? {});
const canEnable  = computed(() => (knowledge.value?.product_count ?? 0) > 0);

const learnedDate = computed(() => {
  const d = knowledge.value?.learned_at;
  return d ? new Date(d).toLocaleString() : null;
});

// ─── Load ─────────────────────────────────────────────────────────────────────
const loadStatus = async () => {
  loading.value = true;
  try {
    const res = await aiEngineService.getStatus();
    if (res.success) status.value = res.data;
  } catch { /* silent */ } finally {
    loading.value = false;
  }
};

onMounted(() => {
  if (proActive.value) loadStatus();
});

// ─── Learning ─────────────────────────────────────────────────────────────────
const startLearning = async () => {
  learning.value = true;
  try {
    const res = await aiEngineService.learn();
    if (res.success) {
      message.success(res.message);
      await loadStatus();
    } else {
      message.error(res.message);
    }
  } catch { message.error(__('Analysis failed. Please try again.', 'giant-checkout-offers-for-woocommerce')); } finally {
    learning.value = false;
  }
};

// ─── Toggle ───────────────────────────────────────────────────────────────────
const toggleAi = async () => {
  toggling.value = true;
  try {
    const res = await aiEngineService.toggle(!aiEnabled.value);
    if (res.success) {
      message.success(res.message);
      await loadStatus();
    } else {
      message.error(res.message);
    }
  } catch { message.error(__('Failed to toggle AI mode.', 'giant-checkout-offers-for-woocommerce')); } finally {
    toggling.value = false;
  }
};
</script>

<template>
  <!-- Pro gate -->
  <div v-if="!proActive" class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-20 tw-text-center">
    <div class="tw-w-16 tw-h-16 tw-bg-gradient-to-br tw-from-purple-500 tw-to-indigo-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mb-5 tw-shadow-lg">
      <svg class="tw-w-8 tw-h-8 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
      </svg>
    </div>
    <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-2">{{ __('AI Engine is a Pro Feature', 'giant-checkout-offers-for-woocommerce') }}</h2>
    <p class="tw-text-gray-500 tw-max-w-md tw-mb-6">{{ __('Let Claude analyze your product catalog and automatically show the most relevant bump to each customer. Upgrade to Smart Order Bump Pro to unlock AI-powered selection.', 'giant-checkout-offers-for-woocommerce') }}</p>
    <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-1.5 tw-bg-purple-100 tw-text-purple-700 tw-text-sm tw-font-semibold tw-rounded-full">
      <svg class="tw-w-4 tw-h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
      {{ __('Available in Smart Order Bump Pro', 'giant-checkout-offers-for-woocommerce') }}
    </span>
  </div>

  <div v-else class="tw-space-y-6 tw-max-w-3xl">

    <!-- Header -->
    <div class="tw-flex tw-items-center tw-justify-between">
      <div>
        <h2 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-m-0">{{ __('AI Engine', 'giant-checkout-offers-for-woocommerce') }}</h2>
        <p class="tw-text-sm tw-text-gray-500 tw-mt-1 tw-m-0">{{ __('Powered by Claude — learns your products and shows the most relevant bump automatically', 'giant-checkout-offers-for-woocommerce') }}</p>
      </div>
      <span
        class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-semibold"
        :class="aiEnabled ? 'tw-bg-green-100 tw-text-green-700' : 'tw-bg-gray-100 tw-text-gray-500'"
      >
        <span class="tw-w-2 tw-h-2 tw-rounded-full" :class="aiEnabled ? 'tw-bg-green-500' : 'tw-bg-gray-400'"></span>
        {{ aiEnabled ? __('AI Active', 'giant-checkout-offers-for-woocommerce') : __('AI Inactive', 'giant-checkout-offers-for-woocommerce') }}
      </span>
    </div>

    <div v-if="loading && !status" class="tw-text-center tw-py-16 tw-text-gray-400 tw-text-sm">{{ __('Loading…', 'giant-checkout-offers-for-woocommerce') }}</div>

    <template v-if="status">

      <!-- ── Step 1: Analyze Products ─────────────────────────────────────── -->
      <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm tw-p-6">
        <div class="tw-flex tw-items-start tw-justify-between tw-mb-4">
          <div class="tw-flex tw-items-center tw-gap-3">
            <div class="tw-w-8 tw-h-8 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-sm tw-font-bold tw-flex-shrink-0"
              :class="knowledge.product_count > 0 ? 'tw-bg-green-500 tw-text-white' : 'tw-bg-blue-500 tw-text-white'">
              <svg v-if="knowledge.product_count > 0" class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
              </svg>
              <span v-else>1</span>
            </div>
            <div>
              <h3 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-m-0">{{ __('Analyze Products', 'giant-checkout-offers-for-woocommerce') }}</h3>
              <p class="tw-text-xs tw-text-gray-500 tw-m-0 tw-mt-0.5">{{ __('Claude analyzes your catalog and maps each product to the best bump offer', 'giant-checkout-offers-for-woocommerce') }}</p>
            </div>
          </div>
          <button @click="startLearning" :disabled="learning"
            class="tw-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-purple-600 tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg hover:tw-bg-purple-700 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-transition-colors tw-flex-shrink-0">
            <svg v-if="!learning" class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            <svg v-else class="tw-w-4 tw-h-4 tw-animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ learning ? __('Analyzing…', 'giant-checkout-offers-for-woocommerce') : (knowledge.product_count > 0 ? __('Re-analyze', 'giant-checkout-offers-for-woocommerce') : __('Analyze Products', 'giant-checkout-offers-for-woocommerce')) }}
          </button>
        </div>

        <!-- Knowledge stats -->
        <div v-if="knowledge.product_count > 0" class="tw-grid tw-grid-cols-3 tw-gap-3">
          <div class="tw-bg-gray-50 tw-rounded-lg tw-p-3 tw-text-center">
            <div class="tw-text-2xl tw-font-bold tw-text-gray-900">{{ knowledge.product_count }}</div>
            <div class="tw-text-xs tw-text-gray-500 tw-mt-0.5">{{ __('Products analyzed', 'giant-checkout-offers-for-woocommerce') }}</div>
          </div>
          <div class="tw-bg-gray-50 tw-rounded-lg tw-p-3 tw-text-center">
            <div class="tw-text-2xl tw-font-bold tw-text-gray-900">{{ knowledge.bump_count }}</div>
            <div class="tw-text-xs tw-text-gray-500 tw-mt-0.5">{{ __('Bumps mapped', 'giant-checkout-offers-for-woocommerce') }}</div>
          </div>
          <div class="tw-bg-gray-50 tw-rounded-lg tw-p-3 tw-text-center">
            <div class="tw-text-xs tw-font-semibold tw-text-gray-700 tw-mt-1">{{ __('Last analyzed', 'giant-checkout-offers-for-woocommerce') }}</div>
            <div class="tw-text-xs tw-text-gray-500 tw-mt-0.5">{{ learnedDate }}</div>
          </div>
        </div>

        <!-- Claude strategy note -->
        <div v-if="knowledge.summary" class="tw-mt-3 tw-bg-purple-50 tw-border tw-border-purple-100 tw-rounded-lg tw-px-4 tw-py-3">
          <p class="tw-text-xs tw-text-purple-700 tw-m-0 tw-italic">"{{ knowledge.summary }}"</p>
          <p class="tw-text-xs tw-text-purple-400 tw-m-0 tw-mt-1">{{ __("— Claude's matching strategy", 'giant-checkout-offers-for-woocommerce') }}</p>
        </div>

        <!-- Auto-update notice -->
        <div v-if="knowledge.product_count > 0" class="tw-mt-3 tw-flex tw-items-center tw-gap-2 tw-text-xs tw-text-blue-600 tw-bg-blue-50 tw-rounded-lg tw-px-3 tw-py-2">
          <svg class="tw-w-3.5 tw-h-3.5 tw-flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ __('Auto-updates when new products are published — no manual re-analysis needed', 'giant-checkout-offers-for-woocommerce') }}
        </div>

        <!-- Analyzing indicator -->
        <div v-if="learning" class="tw-mt-4 tw-space-y-2">
          <p class="tw-text-sm tw-text-gray-500 tw-m-0 tw-flex tw-items-center tw-gap-2">
            <svg class="tw-w-4 tw-h-4 tw-animate-spin tw-text-purple-500" fill="none" viewBox="0 0 24 24">
              <circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ __('Sending your catalog to Claude for analysis. This may take up to 30 seconds…', 'giant-checkout-offers-for-woocommerce') }}
          </p>
          <div class="tw-w-full tw-bg-gray-200 tw-rounded-full tw-h-1.5 tw-overflow-hidden">
            <div class="tw-h-full tw-bg-purple-500 tw-rounded-full tw-animate-pulse" style="width:65%"></div>
          </div>
        </div>
      </div>

      <!-- ── Step 2: Enable AI ─────────────────────────────────────────────── -->
      <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm tw-p-6"
        :class="!canEnable ? 'tw-opacity-50 tw-pointer-events-none' : ''">
        <div class="tw-flex tw-items-center tw-justify-between">
          <div class="tw-flex tw-items-center tw-gap-3">
            <div class="tw-w-8 tw-h-8 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-sm tw-font-bold tw-flex-shrink-0"
              :class="aiEnabled ? 'tw-bg-green-500 tw-text-white' : 'tw-bg-blue-500 tw-text-white'">
              <svg v-if="aiEnabled" class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
              </svg>
              <span v-else>2</span>
            </div>
            <div>
              <h3 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-m-0">{{ __('Enable AI-Powered Selection', 'giant-checkout-offers-for-woocommerce') }}</h3>
              <p class="tw-text-xs tw-text-gray-500 tw-m-0 tw-mt-0.5">
                {{ aiEnabled
                  ? __("AI is active — Claude's recommendations are selecting bumps automatically", 'giant-checkout-offers-for-woocommerce')
                  : __("Turn on to let Claude pick the best bump for each customer's cart", 'giant-checkout-offers-for-woocommerce') }}
              </p>
            </div>
          </div>
          <button type="button" @click="toggleAi" :disabled="toggling || !canEnable"
            class="tw-relative tw-inline-flex tw-h-6 tw-w-11 tw-flex-shrink-0 tw-cursor-pointer tw-rounded-full tw-border-2 tw-border-transparent tw-transition-colors tw-duration-200 focus:tw-outline-none disabled:tw-opacity-50"
            :class="aiEnabled ? 'tw-bg-green-500' : 'tw-bg-gray-300'">
            <span class="tw-inline-block tw-h-5 tw-w-5 tw-transform tw-rounded-full tw-bg-white tw-shadow tw-transition tw-duration-200"
              :style="{ transform: aiEnabled ? 'translateX(20px)' : 'translateX(0)' }"></span>
          </button>
        </div>

        <div v-if="!canEnable" class="tw-mt-3 tw-flex tw-items-center tw-gap-2 tw-text-xs tw-text-amber-600 tw-bg-amber-50 tw-rounded-lg tw-px-3 tw-py-2">
          <svg class="tw-w-4 tw-h-4 tw-flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          {{ __('Complete Step 1 first — analyze your products', 'giant-checkout-offers-for-woocommerce') }}
        </div>
      </div>

      <!-- ── How it works ───────────────────────────────────────────────────── -->
      <div class="tw-bg-gradient-to-br tw-from-blue-50 tw-to-purple-50 tw-rounded-xl tw-border tw-border-blue-100 tw-p-5">
        <h4 class="tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-3 tw-m-0">{{ __('How it works', 'giant-checkout-offers-for-woocommerce') }}</h4>
        <div class="tw-grid tw-grid-cols-3 tw-gap-4 tw-text-center">
          <div v-for="step in [
            { icon: '🧠', title: __('Claude learns', 'giant-checkout-offers-for-woocommerce'), desc: __('Reads all your products and bumps, understands relationships and categories', 'giant-checkout-offers-for-woocommerce') },
            { icon: '🛒', title: __('Customer shops', 'giant-checkout-offers-for-woocommerce'), desc: __('When a customer adds products to cart, AI instantly finds the best match', 'giant-checkout-offers-for-woocommerce') },
            { icon: '⚡', title: __('Smart bump shown', 'giant-checkout-offers-for-woocommerce'), desc: __('The most relevant bump appears automatically — personalized every time', 'giant-checkout-offers-for-woocommerce') },
          ]" :key="step.title">
            <div class="tw-text-2xl tw-mb-1">{{ step.icon }}</div>
            <div class="tw-text-xs tw-font-semibold tw-text-gray-700">{{ step.title }}</div>
            <div class="tw-text-xs tw-text-gray-500 tw-mt-1 tw-leading-relaxed">{{ step.desc }}</div>
          </div>
        </div>
      </div>

    </template>
  </div>
</template>
