<script setup>
import { ref, computed, onMounted } from 'vue';
import licenseService from '../services/licenseService';
import settingsService from '../services/settingsService';
import message from '../utils/message';

const { __ } = wp?.i18n || { __: (t) => t };

const proActive = computed(() => !!window.gcowPluginData?.proActive);

// ─── General Settings ─────────────────────────────────────────────────────────
const variationSwatches = ref(true);
const savingSwatches    = ref(false);

const loadSettings = async () => {
  try {
    const res = await settingsService.get();
    if (res.success && res.data) {
      variationSwatches.value = res.data.variationSwatches !== false;
    }
  } catch { /* silent */ }
};

const toggleSwatches = async (val) => {
  variationSwatches.value = val;
  savingSwatches.value = true;
  try {
    await settingsService.update({ variationSwatches: val });
    message.success(__('Setting saved.', 'giant-checkout-offers-for-woocommerce'));
  } catch {
    message.error(__('Failed to save setting.', 'giant-checkout-offers-for-woocommerce'));
  } finally {
    savingSwatches.value = false;
  }
};

onMounted(() => {
  loadSettings();
});

// ─── State ────────────────────────────────────────────────────────────────────
const licenseKey    = ref('');
const licenseStatus = ref('unregistered');
const expires       = ref('');
const loading       = ref(false);
const activating    = ref(false);
const deactivating  = ref(false);
const confirmDeact  = ref(false);

// ─── Computed ─────────────────────────────────────────────────────────────────
const isActive  = computed(() => licenseStatus.value === 'valid');
const expiresLabel = computed(() => {
  if (!expires.value || expires.value === 'lifetime') return __('Lifetime', 'giant-checkout-offers-for-woocommerce');
  return new Date(expires.value).toLocaleDateString();
});

// ─── Load ─────────────────────────────────────────────────────────────────────
const loadStatus = async () => {
  loading.value = true;
  try {
    const res = await licenseService.getStatus();
    if (res.success) {
      licenseStatus.value = res.license_status ?? 'unregistered';
      licenseKey.value    = licenseStatus.value === 'valid' ? res.license_key : '';
      expires.value       = res.expires ?? '';
    }
  } catch { /* silent */ } finally {
    loading.value = false;
  }
};

onMounted(() => {
  if (proActive.value) loadStatus();
});

// ─── Activate ─────────────────────────────────────────────────────────────────
const activate = async () => {
  if (!licenseKey.value.trim()) {
    message.warning(__('Please enter your license key.', 'giant-checkout-offers-for-woocommerce'));
    return;
  }
  activating.value = true;
  try {
    const res = await licenseService.activate(licenseKey.value.trim());
    if (res.success) {
      message.success(__('License activated successfully.', 'giant-checkout-offers-for-woocommerce'));
      await loadStatus();
    } else {
      message.error(res.message || __('Activation failed. Please check your license key.', 'giant-checkout-offers-for-woocommerce'));
    }
  } catch (err) {
    message.error(err?.message || __('Activation failed. Please try again.', 'giant-checkout-offers-for-woocommerce'));
  } finally {
    activating.value = false;
  }
};

// ─── Deactivate ───────────────────────────────────────────────────────────────
const deactivate = async () => {
  confirmDeact.value = false;
  deactivating.value = true;
  try {
    const res = await licenseService.deactivate();
    if (res.success) {
      message.success(__('License deactivated.', 'giant-checkout-offers-for-woocommerce'));
      licenseKey.value    = '';
      licenseStatus.value = 'unregistered';
      expires.value       = '';
    } else {
      message.error(res.message || __('Deactivation failed.', 'giant-checkout-offers-for-woocommerce'));
    }
  } catch {
    message.error(__('Deactivation failed. Please try again.', 'giant-checkout-offers-for-woocommerce'));
  } finally {
    deactivating.value = false;
  }
};
</script>

<template>
  <div class="tw-space-y-6 tw-max-w-2xl">

    <!-- ── License Section (Pro only) ──────────────────────────────────────── -->
    <div v-if="proActive" class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm">

      <!-- Header -->
      <div class="tw-flex tw-items-center tw-justify-between tw-px-6 tw-py-4 tw-border-b tw-border-gray-100">
        <div class="tw-flex tw-items-center tw-gap-3">
          <div class="tw-w-8 tw-h-8 tw-bg-blue-50 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
            <svg class="tw-w-4 tw-h-4 tw-text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
          </div>
          <div>
            <h2 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-m-0">
              {{ __('License', 'giant-checkout-offers-for-woocommerce') }}
            </h2>
            <p class="tw-text-xs tw-text-gray-500 tw-m-0">
              {{ __('Manage your Giant Checkout Offers Pro license', 'giant-checkout-offers-for-woocommerce') }}
            </p>
          </div>
        </div>

        <!-- Status Badge -->
        <span
          class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs tw-font-semibold"
          :class="isActive ? 'tw-bg-green-100 tw-text-green-700' : 'tw-bg-gray-100 tw-text-gray-500'"
        >
          <span class="tw-w-1.5 tw-h-1.5 tw-rounded-full" :class="isActive ? 'tw-bg-green-500' : 'tw-bg-gray-400'"></span>
          {{ isActive ? __('Active', 'giant-checkout-offers-for-woocommerce') : __('Inactive', 'giant-checkout-offers-for-woocommerce') }}
        </span>
      </div>

      <!-- Body -->
      <div class="tw-px-6 tw-py-5">

        <!-- Loading -->
        <div v-if="loading" class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-text-gray-400">
          <svg class="tw-w-4 tw-h-4 tw-animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ __('Loading license status…', 'giant-checkout-offers-for-woocommerce') }}
        </div>

        <!-- Active State -->
        <template v-else-if="isActive">
          <div class="tw-flex tw-items-start tw-gap-4 tw-bg-green-50 tw-rounded-lg tw-p-4 tw-mb-4">
            <div class="tw-w-9 tw-h-9 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
              <svg class="tw-w-5 tw-h-5 tw-text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div class="tw-flex-1 tw-min-w-0">
              <p class="tw-text-sm tw-font-semibold tw-text-green-800 tw-m-0">
                {{ __('Your license is active', 'giant-checkout-offers-for-woocommerce') }}
              </p>
              <p class="tw-text-xs tw-text-green-600 tw-m-0 tw-mt-0.5">
                {{ __('Expires:', 'giant-checkout-offers-for-woocommerce') }} <strong>{{ expiresLabel }}</strong>
              </p>
            </div>
          </div>

          <!-- Deactivate -->
          <div v-if="!confirmDeact">
            <button
              @click="confirmDeact = true"
              class="tw-text-sm tw-text-gray-400 hover:tw-text-red-500 tw-transition-colors tw-underline tw-underline-offset-2"
            >
              {{ __('Deactivate license', 'giant-checkout-offers-for-woocommerce') }}
            </button>
          </div>
          <div v-else class="tw-flex tw-items-center tw-gap-3 tw-mt-1">
            <span class="tw-text-sm tw-text-gray-600">{{ __('Are you sure?', 'giant-checkout-offers-for-woocommerce') }}</span>
            <button
              @click="deactivate"
              :disabled="deactivating"
              class="tw-text-sm tw-font-semibold tw-text-red-600 hover:tw-text-red-700 disabled:tw-opacity-50 tw-transition-colors"
            >
              {{ deactivating ? __('Deactivating…', 'giant-checkout-offers-for-woocommerce') : __('Yes, deactivate', 'giant-checkout-offers-for-woocommerce') }}
            </button>
            <button
              @click="confirmDeact = false"
              class="tw-text-sm tw-text-gray-400 hover:tw-text-gray-600 tw-transition-colors"
            >
              {{ __('Cancel', 'giant-checkout-offers-for-woocommerce') }}
            </button>
          </div>
        </template>

        <!-- Inactive State -->
        <template v-else>
          <p class="tw-text-sm tw-text-gray-600 tw-mb-4 tw-m-0">
            {{ __('Enter your license key to unlock pro features including Analytics and AI Engine.', 'giant-checkout-offers-for-woocommerce') }}
          </p>
          <div class="tw-flex tw-gap-3">
            <input
              v-model="licenseKey"
              type="text"
              :placeholder="__('XXXX-XXXX-XXXX-XXXX', 'giant-checkout-offers-for-woocommerce')"
              class="tw-flex-1 tw-min-w-0 tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-gray-900 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-transparent tw-transition"
              @keyup.enter="activate"
            />
            <button
              @click="activate"
              :disabled="activating"
              class="tw-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2.5 tw-bg-blue-600 tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg hover:tw-bg-blue-700 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-transition-colors tw-flex-shrink-0"
            >
              <svg v-if="activating" class="tw-w-4 tw-h-4 tw-animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ activating ? __('Activating…', 'giant-checkout-offers-for-woocommerce') : __('Activate', 'giant-checkout-offers-for-woocommerce') }}
            </button>
          </div>
        </template>

      </div>
    </div>

    <!-- ── General Settings ─────────────────────────────────────────────────── -->
    <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm">
      <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100">
        <h2 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-m-0">
          {{ __('General', 'giant-checkout-offers-for-woocommerce') }}
        </h2>
        <p class="tw-text-xs tw-text-gray-500 tw-m-0 tw-mt-0.5">
          {{ __('Global display options for all order bumps', 'giant-checkout-offers-for-woocommerce') }}
        </p>
      </div>

      <div class="tw-px-6 tw-py-5">
        <div class="tw-flex tw-items-center tw-justify-between tw-gap-4">
          <div class="tw-flex-1 tw-min-w-0">
            <p class="tw-text-sm tw-font-medium tw-text-gray-800 tw-m-0">
              {{ __('Variation Swatches', 'giant-checkout-offers-for-woocommerce') }}
            </p>
            <p class="tw-text-xs tw-text-gray-500 tw-m-0 tw-mt-0.5">
              {{ __('Show clickable swatch buttons instead of dropdowns for product variations.', 'giant-checkout-offers-for-woocommerce') }}
            </p>
          </div>

          <button
            type="button"
            role="switch"
            :aria-checked="variationSwatches"
            :disabled="savingSwatches"
            @click="toggleSwatches(!variationSwatches)"
            class="tw-relative tw-inline-flex tw-h-6 tw-w-11 tw-flex-shrink-0 tw-rounded-full tw-border-2 tw-border-transparent tw-transition-colors tw-duration-200 tw-cursor-pointer disabled:tw-opacity-50 disabled:tw-cursor-not-allowed"
            :style="{ backgroundColor: variationSwatches ? '#3b82f6' : '#d1d5db' }"
          >
            <span
              class="tw-pointer-events-none tw-inline-block tw-h-5 tw-w-5 tw-rounded-full tw-bg-white tw-shadow tw-ring-0 tw-transition-transform tw-duration-200"
              :style="{ transform: variationSwatches ? 'translateX(20px)' : 'translateX(0px)' }"
            ></span>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>
