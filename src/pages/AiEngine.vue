<script setup>
import { ref, onMounted, computed } from 'vue';
import aiEngineService from '../services/aiEngineService';
import settingsService from '../services/settingsService';
import message from '../utils/message';

const { __ } = wp?.i18n || { __: (t) => t };

// ─── State ────────────────────────────────────────────────────────────────────
const status   = ref(null);
const loading  = ref(false);
const learning = ref(false);
const toggling = ref(false);

// ─── AI API Keys ──────────────────────────────────────────────────────────────
const aiProviders = [
  { id: 'openai',    name: 'ChatGPT',  company: 'OpenAI',     placeholder: 'sk-proj-...',       iconBg: '#f0fdf4', iconColor: '#10a37f' },
  { id: 'anthropic', name: 'Claude',   company: 'Anthropic',  placeholder: 'sk-ant-api03-...',  iconBg: '#fff7ed', iconColor: '#c96442' },
  { id: 'gemini',    name: 'Gemini',   company: 'Google',     placeholder: 'AIzaSy...',         iconBg: '#eff6ff', iconColor: '#4285f4' },
];

const aiKeys = ref({
  openai:    { connected: false, preview: '', input: '', visible: false, saving: false, removing: false },
  anthropic: { connected: false, preview: '', input: '', visible: false, saving: false, removing: false },
  gemini:    { connected: false, preview: '', input: '', visible: false, saving: false, removing: false },
});

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
  if (proActive.value) {
    loadStatus();
    loadProductSuggestionSetting();
  }
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
      message.error(res.message || __('Analysis failed.', 'giant-checkout-offers-for-woocommerce'));
    }
  } catch (err) {
    const msg = err?.message || err?.data?.message || JSON.stringify(err);
    message.error(msg || __('Analysis failed. Please try again.', 'giant-checkout-offers-for-woocommerce'));
  } finally {
    learning.value = false;
  }
};

// ─── Auto Product Suggestion ──────────────────────────────────────────────────
const productSuggestion        = ref(false);
const savingProductSuggestion  = ref(false);

const loadProductSuggestionSetting = async () => {
  try {
    const res = await settingsService.get();
    if (res.success && res.data) {
      productSuggestion.value = !!res.data.aiProductSuggestion;

      const keys = res.data.aiApiKeys || {};
      ['openai', 'anthropic', 'gemini'].forEach(id => {
        if (keys[id]) {
          aiKeys.value[id].connected = !!keys[id].connected;
          aiKeys.value[id].preview   = keys[id].preview || '';
        }
      });
    }
  } catch { /* silent */ }
};

const connectKey = async (id) => {
  const k = aiKeys.value[id];
  if (!k.input.trim()) {
    message.warning(__('Please enter an API key.', 'giant-checkout-offers-for-woocommerce'));
    return;
  }
  k.saving = true;
  try {
    // Verify the key works before saving
    const verify = await aiEngineService.verifyKey(id, k.input.trim());
    if (!verify.success) {
      message.error(verify.message || __('Invalid API key. Please check and try again.', 'giant-checkout-offers-for-woocommerce'));
      return;
    }

    // Key is valid — save it
    const res = await settingsService.update({ aiApiKeys: { [id]: k.input.trim() } });
    if (res.success) {
      message.success(__('API key connected successfully.', 'giant-checkout-offers-for-woocommerce'));
      k.input   = '';
      k.visible = false;
      await loadProductSuggestionSetting();
    } else {
      message.error(res.message || __('Failed to save key.', 'giant-checkout-offers-for-woocommerce'));
    }
  } catch (err) {
    message.error(err?.message || __('Failed to connect key.', 'giant-checkout-offers-for-woocommerce'));
  } finally {
    k.saving = false;
  }
};

const removeKey = async (id) => {
  const k = aiKeys.value[id];
  k.removing = true;
  try {
    const res = await settingsService.update({ aiApiKeys: { [id]: '' } });
    if (res.success) {
      message.success(__('API key removed.', 'giant-checkout-offers-for-woocommerce'));
      await loadProductSuggestionSetting();
    }
  } catch {
    message.error(__('Failed to remove key.', 'giant-checkout-offers-for-woocommerce'));
  } finally {
    k.removing = false;
  }
};

const toggleProductSuggestion = async (val) => {
  productSuggestion.value = val;
  savingProductSuggestion.value = true;
  try {
    await settingsService.update({ aiProductSuggestion: val });
    message.success(__('Setting saved.', 'giant-checkout-offers-for-woocommerce'));
  } catch {
    message.error(__('Failed to save setting.', 'giant-checkout-offers-for-woocommerce'));
  } finally {
    savingProductSuggestion.value = false;
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

      <!-- ── AI API Keys ─────────────────────────────────────────────────── -->
      <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100">
          <div class="tw-flex tw-items-center tw-gap-3">
            <div class="tw-w-8 tw-h-8 tw-bg-violet-50 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
              <svg class="tw-w-4 tw-h-4 tw-text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
              </svg>
            </div>
            <div>
              <h2 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-m-0">
                {{ __('AI API Keys', 'giant-checkout-offers-for-woocommerce') }}
              </h2>
              <p class="tw-text-xs tw-text-gray-500 tw-m-0 tw-mt-0.5">
                {{ __('Connect your own AI provider keys to power AI features', 'giant-checkout-offers-for-woocommerce') }}
              </p>
            </div>
          </div>
        </div>

        <div class="tw-divide-y tw-divide-gray-100">
          <div v-for="p in aiProviders" :key="p.id" class="tw-px-6 tw-py-5">

            <!-- Top row: icon + name + status badge -->
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
              <div class="tw-flex tw-items-center tw-gap-3">
                <div class="tw-w-9 tw-h-9 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-flex-shrink-0"
                  :style="{ backgroundColor: p.iconBg, color: p.iconColor }">
                  <svg v-if="p.id === 'openai'" viewBox="0 0 24 24" class="tw-w-5 tw-h-5" fill="currentColor">
                    <path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786A4.504 4.504 0 0 1 2.34 7.872zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.667zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.83-2.787a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08L8.704 5.46a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/>
                  </svg>
                  <svg v-else-if="p.id === 'anthropic'" viewBox="0 0 24 24" class="tw-w-5 tw-h-5" fill="currentColor">
                    <path d="M13.827 3.52h-3.654L5.063 20.48h3.399l1.04-2.96h5.016l1.04 2.96h3.399L13.827 3.52zm-3.565 11.333 1.728-4.922 1.729 4.922H10.26z"/>
                  </svg>
                  <svg v-else-if="p.id === 'gemini'" viewBox="0 0 24 24" class="tw-w-5 tw-h-5" fill="currentColor">
                    <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.997-4.674L7.599 12l3.404-3.326L12.416 12l-1.413 5.326zM12.997 6.674L16.401 12l-3.404 3.326L11.584 12l1.413-5.326z"/>
                  </svg>
                </div>
                <div>
                  <p class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-m-0">{{ p.name }}</p>
                  <p class="tw-text-xs tw-text-gray-400 tw-m-0">{{ p.company }}</p>
                </div>
              </div>
              <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium"
                :class="aiKeys[p.id].connected ? 'tw-bg-green-50 tw-text-green-700' : 'tw-bg-gray-100 tw-text-gray-400'">
                <span class="tw-w-1.5 tw-h-1.5 tw-rounded-full"
                  :class="aiKeys[p.id].connected ? 'tw-bg-green-500' : 'tw-bg-gray-300'"></span>
                {{ aiKeys[p.id].connected
                  ? __('Connected', 'giant-checkout-offers-for-woocommerce')
                  : __('Not connected', 'giant-checkout-offers-for-woocommerce') }}
              </span>
            </div>

            <!-- Connected: preview + remove -->
            <div v-if="aiKeys[p.id].connected" class="tw-flex tw-items-center tw-gap-3">
              <div class="tw-flex-1 tw-min-w-0 tw-flex tw-items-center tw-gap-2 tw-px-3.5 tw-py-2.5 tw-bg-gray-50 tw-rounded-lg tw-border tw-border-gray-200">
                <svg class="tw-w-3.5 tw-h-3.5 tw-text-gray-400 tw-flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                <span class="tw-text-sm tw-text-gray-500 tw-font-mono tw-tracking-wider tw-truncate">{{ aiKeys[p.id].preview }}</span>
              </div>
              <button @click="removeKey(p.id)" :disabled="aiKeys[p.id].removing"
                class="tw-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-2.5 tw-text-sm tw-text-red-500 hover:tw-text-red-600 hover:tw-bg-red-50 tw-rounded-lg tw-transition-colors tw-flex-shrink-0 disabled:tw-opacity-50">
                <svg v-if="aiKeys[p.id].removing" class="tw-w-3.5 tw-h-3.5 tw-animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <svg v-else class="tw-w-3.5 tw-h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('Remove', 'giant-checkout-offers-for-woocommerce') }}
              </button>
            </div>

            <!-- Not connected: input + connect -->
            <div v-else class="tw-flex tw-items-center tw-gap-2">
              <div class="tw-relative tw-flex-1 tw-min-w-0">
                <input
                  :type="aiKeys[p.id].visible ? 'text' : 'password'"
                  v-model="aiKeys[p.id].input"
                  :placeholder="p.placeholder"
                  @keyup.enter="connectKey(p.id)"
                  class="tw-w-full tw-pl-3.5 tw-pr-10 tw-py-2.5 tw-text-sm tw-font-mono tw-text-gray-900 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-border-transparent tw-transition"
                  :style="{ '--tw-ring-color': p.iconColor + '66' }"
                />
                <button type="button" @click="aiKeys[p.id].visible = !aiKeys[p.id].visible"
                  class="tw-absolute tw-right-3 tw-top-1/2 -tw-translate-y-1/2 tw-text-gray-400 hover:tw-text-gray-600 tw-transition-colors">
                  <svg v-if="!aiKeys[p.id].visible" class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  <svg v-else class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                  </svg>
                </button>
              </div>
              <button @click="connectKey(p.id)" :disabled="aiKeys[p.id].saving || !aiKeys[p.id].input.trim()"
                class="tw-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-2.5 tw-text-sm tw-font-semibold tw-text-white tw-rounded-lg tw-transition-colors tw-flex-shrink-0 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed"
                :style="{ backgroundColor: aiKeys[p.id].saving || !aiKeys[p.id].input.trim() ? '#9ca3af' : p.iconColor }">
                <svg v-if="aiKeys[p.id].saving" class="tw-w-3.5 tw-h-3.5 tw-animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ aiKeys[p.id].saving ? __('Saving…', 'giant-checkout-offers-for-woocommerce') : __('Connect', 'giant-checkout-offers-for-woocommerce') }}
              </button>
            </div>

          </div>
        </div>
      </div>

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

      <!-- ── Auto Product Suggestion ──────────────────────────────────────── -->
      <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm tw-p-6">
        <div class="tw-flex tw-items-start tw-justify-between tw-gap-4">
          <div class="tw-flex tw-items-start tw-gap-3 tw-flex-1 tw-min-w-0">
            <div class="tw-w-8 tw-h-8 tw-rounded-lg tw-bg-indigo-50 tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
              <svg class="tw-w-4 tw-h-4 tw-text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
              </svg>
            </div>
            <div>
              <h3 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-m-0">
                {{ __('Auto Product Suggestion', 'giant-checkout-offers-for-woocommerce') }}
              </h3>
              <p class="tw-text-xs tw-text-gray-500 tw-m-0 tw-mt-0.5 tw-leading-relaxed">
                {{ __('Show a related-product slider after the short description on single product pages. Mobile shows 1 product; desktop shows 2.5 products in view.', 'giant-checkout-offers-for-woocommerce') }}
              </p>
            </div>
          </div>
          <button type="button"
            :disabled="savingProductSuggestion"
            @click="toggleProductSuggestion(!productSuggestion)"
            class="tw-relative tw-inline-flex tw-h-6 tw-w-11 tw-flex-shrink-0 tw-cursor-pointer tw-rounded-full tw-border-2 tw-border-transparent tw-transition-colors tw-duration-200 focus:tw-outline-none disabled:tw-opacity-50"
            :class="productSuggestion ? 'tw-bg-indigo-500' : 'tw-bg-gray-300'">
            <span class="tw-inline-block tw-h-5 tw-w-5 tw-rounded-full tw-bg-white tw-shadow tw-transition tw-duration-200"
              :style="{ transform: productSuggestion ? 'translateX(20px)' : 'translateX(0)' }"></span>
          </button>
        </div>

        <div v-if="productSuggestion" class="tw-mt-4 tw-flex tw-items-center tw-gap-2 tw-text-xs tw-text-indigo-600 tw-bg-indigo-50 tw-rounded-lg tw-px-3 tw-py-2">
          <svg class="tw-w-3.5 tw-h-3.5 tw-flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          {{ __('Active — related products are now shown on single product pages', 'giant-checkout-offers-for-woocommerce') }}
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
