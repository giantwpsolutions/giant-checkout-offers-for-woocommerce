<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { ElDialog, ElTabs, ElTabPane, ElButton } from 'element-plus';
import { Close, Plus } from '@element-plus/icons-vue';
import { previewComponents, templateList, getDefaultStyles, CART_POSITIONS } from '../config/templates';
import { useTemplateSettings } from '../composables/useTemplateSettings';
import productService from '../services/productService';
import categoryService from '../services/categoryService';

const { __ } = wp?.i18n || { __: (t) => t };

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  editData: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['update:visible', 'save']);

const activeTab = ref('product');
const isPro = ref(false); // TODO: connect to actual license check

// Form data
const bumpForm = ref({
  id: null,
  name: '',
  product: '',
  variationId: 0,
  variationAttrs: {},
  discountType: 'percentage',
  discountValue: 10,
  position: 'checkout_before_payment',
  displayCondition: 'all',
  selectedProducts: [],
  selectedCategories: [],
  minCartTotal: 50,
  excludeBumpProduct: true,
  title: '',
  description: 'Get this exclusive offer only available at checkout!',
  buttonText: 'Yes, Add to Order!',
});

// Check if we're in edit mode
const isEditMode = computed(() => props.editData !== null);

// Watch editData to populate form when editing
watch(() => props.editData, (newData) => {
  if (newData) {
    bumpForm.value = {
      id: newData.id || null,
      name: newData.name || '',
      product: newData.product || '',
      variationId: newData.variationId || 0,
      variationAttrs: newData.variationAttrs || {},
      discountType: newData.discountType || 'percentage',
      discountValue: newData.discountValue || 10,
      position: newData.position || 'checkout_before_payment',
      displayCondition: newData.displayCondition || 'all',
      selectedProducts: newData.selectedProducts || [],
      selectedCategories: newData.selectedCategories || [],
      minCartTotal: newData.minCartTotal || 50,
      excludeBumpProduct: newData.excludeBumpProduct !== false,
      title: newData.title || '',
      description: newData.description || 'Get this exclusive offer only available at checkout!',
      buttonText: newData.buttonText || 'Yes, Add to Order!',
    };
    if (newData.template) {
      activeTemplate.value = newData.template;
    }
  } else {
    bumpForm.value = {
      id: null,
      name: '',
      product: '',
      variationId: 0,
      variationAttrs: {},
      discountType: 'percentage',
      discountValue: 10,
      position: 'checkout_before_payment',
      displayCondition: 'all',
      selectedProducts: [],
      selectedCategories: [],
      minCartTotal: 50,
      excludeBumpProduct: true,
      title: '',
      description: 'Get this exclusive offer only available at checkout!',
      buttonText: 'Yes, Add to Order!',
    };
  }
}, { immediate: true });

// Shared template settings
const { activeTemplate, currentTemplateStyles, loadSettings: loadActiveTemplate, getStylesForTemplate } = useTemplateSettings();

// Templates filtered by selected position context
const isCartPosition = computed(() => CART_POSITIONS.includes(bumpForm.value.position));

const availableTemplates = computed(() => {
  return templateList.filter(t =>
    t.context === 'both' ||
    (isCartPosition.value ? t.context === 'cart' : t.context === 'checkout')
  );
});

const selectTemplate = (tpl) => {
  activeTemplate.value = tpl.id;
};

watch(isCartPosition, () => {
  const stillValid = availableTemplates.value.some(t => t.id === activeTemplate.value);
  if (!stillValid) {
    activeTemplate.value = availableTemplates.value[0]?.id || 'standard';
  }
});

onMounted(() => {
  loadActiveTemplate();
  loadProducts();
  loadCategories();
});

// Products
const productOptions = ref([]);
const productDropdownOpen = ref(false);
const productTriggerRef = ref(null);

const loadProducts = async () => {
  try {
    const res = await productService.getAll({ per_page: 100 });
    if (res?.products && Array.isArray(res.products)) {
      productOptions.value = res.products;
    }
  } catch {
    // fallback empty
  }
};

const selectedProduct = computed(() => {
  return productOptions.value.find(p => p.id === bumpForm.value.product) || null;
});

const selectProduct = (product) => {
  bumpForm.value.product = product.id;
  bumpForm.value.variationId = 0;
  bumpForm.value.variationAttrs = {};
  productDropdownOpen.value = false;
};

const selectVariation = (variation) => {
  bumpForm.value.variationId    = variation.id;
  bumpForm.value.variationAttrs = variation.specific_attrs || {};
};

const isVariationSelected = (variation) => {
  if (bumpForm.value.variationId !== variation.id) return false;
  const stored  = bumpForm.value.variationAttrs || {};
  const varAttr = variation.specific_attrs || {};
  return JSON.stringify(stored) === JSON.stringify(varAttr);
};

const isVariableProduct = computed(() => selectedProduct.value?.type === 'variable');
const productVariations = computed(() => selectedProduct.value?.variations || []);

const formatVariationLabel = (variation) => {
  const attrs = variation.attributes || [];
  if (Array.isArray(attrs) && attrs.length > 0) {
    return attrs.map(a => `${a.label}: ${a.value || __('Any', 'giant-checkout-offers-for-woocommerce')}`).join(' / ');
  }
  return `${__('Variation', 'giant-checkout-offers-for-woocommerce')} #${variation.id}`;
};

const productDropdownStyle = computed(() => {
  if (!productTriggerRef.value) return {};
  const rect = productTriggerRef.value.getBoundingClientRect();
  return {
    position: 'fixed',
    top: `${rect.bottom + 4}px`,
    left: `${rect.left}px`,
    width: `${rect.width}px`,
    zIndex: 9999
  };
});

// Position options
const positionOptions = computed(() => [
  { value: 'checkout_before_payment', label: __('Before Payment Methods', 'giant-checkout-offers-for-woocommerce'), pro: false },
  { value: 'checkout_after_payment',  label: __('After Payment Methods', 'giant-checkout-offers-for-woocommerce'),  pro: true },
  { value: 'checkout_before_review',  label: __('Before Order Review', 'giant-checkout-offers-for-woocommerce'),    pro: true },
  { value: 'checkout_after_review',   label: __('After Order Review', 'giant-checkout-offers-for-woocommerce'),     pro: true },
  { value: 'cart_before_totals',      label: __('Cart Page - Before Totals', 'giant-checkout-offers-for-woocommerce'), pro: true },
  { value: 'cart_after_totals',       label: __('Cart Page - After Totals', 'giant-checkout-offers-for-woocommerce'),  pro: true },
]);

// Condition options
const conditionOptions = computed(() => [
  { value: 'all',        label: __('Always show (all orders)', 'giant-checkout-offers-for-woocommerce') },
  { value: 'products',   label: __('Specific products in cart', 'giant-checkout-offers-for-woocommerce') },
  { value: 'categories', label: __('Specific categories in cart', 'giant-checkout-offers-for-woocommerce') },
  { value: 'cart_total', label: __('Cart total above amount', 'giant-checkout-offers-for-woocommerce') },
]);

// Category options
const categoryOptions = ref([]);

const loadCategories = async () => {
  try {
    const res = await categoryService.getAll();
    if (Array.isArray(res)) {
      categoryOptions.value = res.map(c => ({ value: c.id, label: c.name }));
    }
  } catch {
    // fallback empty
  }
};

// Custom dropdown state
const positionDropdownOpen = ref(false);
const positionTriggerRef = ref(null);

const selectedPositionLabel = computed(() => {
  const opt = positionOptions.value.find(o => o.value === bumpForm.value.position);
  return opt ? opt.label : '';
});

const dropdownStyle = computed(() => {
  if (!positionTriggerRef.value) return {};
  const rect = positionTriggerRef.value.getBoundingClientRect();
  return {
    position: 'fixed',
    top: `${rect.bottom + 4}px`,
    left: `${rect.left}px`,
    width: `${rect.width}px`,
    zIndex: 9999
  };
});

const selectPosition = (opt) => {
  if (opt.pro && !isPro.value) return;
  bumpForm.value.position = opt.value;
  positionDropdownOpen.value = false;
};

const selectedProductName = computed(() => {
  return selectedProduct.value ? selectedProduct.value.name : __('Product Name', 'giant-checkout-offers-for-woocommerce');
});

const previewTitle = computed(() => {
  if (bumpForm.value.title) return bumpForm.value.title;
  return selectedProduct.value ? selectedProduct.value.name : __('Product Name', 'giant-checkout-offers-for-woocommerce');
});

const selectedProductImage = computed(() => {
  if (selectedProduct.value?.image?.src) return selectedProduct.value.image.src;
  if (selectedProduct.value?.image?.thumbnail) return selectedProduct.value.image.thumbnail;
  return '';
});

const selectedProductLabel = computed(() => {
  return selectedProduct.value ? selectedProduct.value.name : __('Select a product...', 'giant-checkout-offers-for-woocommerce');
});

const closeDropdowns = () => {
  positionDropdownOpen.value = false;
  productDropdownOpen.value = false;
};

const closeModal = () => {
  emit('update:visible', false);
};

const saveBump = (status = 'active') => {
  emit('save', { ...bumpForm.value, status, template: activeTemplate.value });
  emit('update:visible', false);
};
</script>

<template>
  <ElDialog
    :model-value="visible"
    @update:model-value="$emit('update:visible', $event)"
    title=""
    width="95%"
    style="max-width: 1400px;"
    top="5vh"
    :show-close="false"
    :close-on-click-modal="false"
    class="bump-modal"
    destroy-on-close
    append-to-body
  >
    <!-- Header -->
    <template #header>
      <div class="tw-flex tw-items-center tw-justify-between tw-w-full">
        <div class="tw-flex tw-items-center tw-gap-4">
          <div class="tw-w-12 tw-h-12 tw-bg-gradient-to-br tw-from-blue-500 tw-to-indigo-600 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-shadow-blue-500/25">
            <Plus class="tw-w-6 tw-h-6 tw-text-white" />
          </div>
          <div>
            <h2 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-m-0">
              {{ isEditMode ? __('Edit Order Bump', 'giant-checkout-offers-for-woocommerce') : __('Create Order Bump', 'giant-checkout-offers-for-woocommerce') }}
            </h2>
            <p class="tw-text-sm tw-text-gray-500 tw-m-0 tw-mt-1">{{ __('Configure your bump offer and design', 'giant-checkout-offers-for-woocommerce') }}</p>
          </div>
        </div>
        <ElButton :icon="Close" circle size="large" @click="closeModal" class="tw-w-10 tw-h-10" />
      </div>
    </template>

    <!-- Body -->
    <div class="tw-flex tw-gap-8" style="height: calc(85vh - 200px); min-height: 500px;">
      <!-- Left Panel: Settings -->
      <div class="tw-w-1/2 tw-overflow-y-auto tw-pr-6 tw-pl-2">
        <ElTabs v-model="activeTab" class="bump-tabs">
          <!-- Product Tab -->
          <ElTabPane :label="__('Product', 'giant-checkout-offers-for-woocommerce')" name="product">
            <div class="tw-space-y-7 tw-pt-6 tw-pb-4">
              <!-- Bump Name -->
              <div>
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">{{ __('Bump Name', 'giant-checkout-offers-for-woocommerce') }}</label>
                <input
                  v-model="bumpForm.name"
                  type="text"
                  :placeholder="__('e.g., Summer Sale Bump', 'giant-checkout-offers-for-woocommerce')"
                  class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-text-gray-900 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-border-blue-500 focus:tw-ring-1 focus:tw-ring-blue-500 tw-transition-colors"
                />
                <p class="tw-text-xs tw-text-gray-400 tw-mt-2">{{ __('Internal name for your reference only', 'giant-checkout-offers-for-woocommerce') }}</p>
              </div>

              <!-- Select Product -->
              <div>
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">{{ __('Bump Product', 'giant-checkout-offers-for-woocommerce') }}</label>
                <div
                  ref="productTriggerRef"
                  @click="productDropdownOpen = !productDropdownOpen"
                  class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-bg-white tw-cursor-pointer tw-flex tw-items-center tw-justify-between tw-transition-colors"
                  :class="productDropdownOpen ? 'tw-border-blue-500 tw-ring-1 tw-ring-blue-500' : ''"
                >
                  <div class="tw-flex tw-items-center tw-gap-3 tw-min-w-0">
                    <img
                      v-if="selectedProduct?.image"
                      :src="selectedProduct.image.thumbnail || selectedProduct.image.src"
                      :alt="selectedProduct.name"
                      class="tw-w-8 tw-h-8 tw-rounded tw-object-cover tw-border tw-border-gray-200 tw-flex-shrink-0"
                    />
                    <span :class="selectedProduct ? 'tw-text-gray-900' : 'tw-text-gray-400'" class="tw-truncate">{{ selectedProductLabel }}</span>
                  </div>
                  <svg class="tw-w-4 tw-h-4 tw-text-gray-400 tw-transition-transform tw-flex-shrink-0" :class="productDropdownOpen ? 'tw-rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
                <Teleport to="body">
                  <div v-if="productDropdownOpen" @click="closeDropdowns" class="tw-fixed tw-inset-0" style="z-index: 9998;"></div>
                  <div
                    v-if="productDropdownOpen"
                    :style="productDropdownStyle"
                    class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-lg tw-shadow-lg tw-max-h-64 tw-overflow-y-auto"
                  >
                    <div
                      v-for="product in productOptions"
                      :key="product.id"
                      @click="selectProduct(product)"
                      class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-text-sm tw-cursor-pointer tw-transition-colors tw-border-b tw-border-gray-100 last:tw-border-b-0"
                      :class="bumpForm.product === product.id ? 'tw-bg-blue-50 tw-text-blue-600 tw-font-medium' : 'tw-text-gray-700 hover:tw-bg-blue-50'"
                    >
                      <img
                        v-if="product.image"
                        :src="product.image.thumbnail || product.image.src"
                        :alt="product.name"
                        class="tw-w-9 tw-h-9 tw-rounded tw-object-cover tw-border tw-border-gray-200 tw-flex-shrink-0"
                      />
                      <div class="tw-flex-1 tw-min-w-0">
                        <div class="tw-truncate">{{ product.name }}</div>
                        <div class="tw-text-xs tw-text-gray-400" v-html="product.price_html || ('$' + product.price)"></div>
                      </div>
                    </div>
                    <div v-if="productOptions.length === 0" class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-500 tw-text-center">
                      {{ __('No products found', 'giant-checkout-offers-for-woocommerce') }}
                    </div>
                  </div>
                </Teleport>
                <p class="tw-text-xs tw-text-gray-400 tw-mt-2">{{ __('The product to offer as bump', 'giant-checkout-offers-for-woocommerce') }}</p>
              </div>

              <!-- Variation Selection (only for variable products) -->
              <div v-if="isVariableProduct && productVariations.length > 0">
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">{{ __('Variation', 'giant-checkout-offers-for-woocommerce') }}</label>
                <div class="tw-space-y-2 tw-max-h-52 tw-overflow-y-auto tw-border tw-border-gray-200 tw-rounded-lg tw-p-2">
                  <!-- All Variations option -->
                  <label
                    class="tw-flex tw-items-center tw-gap-3 tw-px-3 tw-py-2 tw-rounded-lg tw-cursor-pointer tw-transition-colors"
                    :class="bumpForm.variationId === 0 ? 'tw-bg-blue-50 tw-border tw-border-blue-300' : 'tw-border tw-border-transparent hover:tw-bg-gray-50'"
                  >
                    <input type="radio" :checked="bumpForm.variationId === 0" @click="bumpForm.variationId = 0; bumpForm.variationAttrs = {}" class="tw-w-4 tw-h-4 tw-accent-blue-500" />
                    <div class="tw-flex-1 tw-min-w-0">
                      <span class="tw-text-sm tw-font-medium tw-text-gray-900">{{ __('All Variations', 'giant-checkout-offers-for-woocommerce') }}</span>
                      <p class="tw-text-xs tw-text-gray-500 tw-m-0">{{ __('Customer selects at checkout', 'giant-checkout-offers-for-woocommerce') }}</p>
                    </div>
                  </label>
                  <!-- Specific variation options -->
                  <label
                    v-for="(variation, idx) in productVariations"
                    :key="variation.id + '-' + idx"
                    class="tw-flex tw-items-center tw-gap-3 tw-px-3 tw-py-2 tw-rounded-lg tw-cursor-pointer tw-transition-colors"
                    :class="isVariationSelected(variation) ? 'tw-bg-blue-50 tw-border tw-border-blue-300' : 'tw-border tw-border-transparent hover:tw-bg-gray-50'"
                    @click="selectVariation(variation)"
                  >
                    <input type="radio" :checked="isVariationSelected(variation)" class="tw-w-4 tw-h-4 tw-accent-blue-500" />
                    <img
                      v-if="variation.image?.src"
                      :src="variation.image.src"
                      :alt="formatVariationLabel(variation)"
                      class="tw-w-8 tw-h-8 tw-rounded tw-object-cover tw-border tw-border-gray-200 tw-flex-shrink-0"
                    />
                    <div class="tw-flex-1 tw-min-w-0">
                      <span class="tw-text-sm tw-font-medium tw-text-gray-900">{{ formatVariationLabel(variation) }}</span>
                      <span class="tw-text-xs tw-text-gray-500 tw-ml-2" v-html="variation.price_html || ('$' + variation.price)"></span>
                    </div>
                    <span
                      v-if="variation.stock_status !== 'instock'"
                      class="tw-text-xs tw-text-red-500 tw-font-medium tw-flex-shrink-0"
                    >{{ __('Out of stock', 'giant-checkout-offers-for-woocommerce') }}</span>
                  </label>
                </div>
                <p class="tw-text-xs tw-text-gray-400 tw-mt-2">
                  <span v-if="bumpForm.variationId === 0">{{ __('Customer will choose a variation on the checkout page', 'giant-checkout-offers-for-woocommerce') }}</span>
                  <span v-else>{{ __('Only this specific variation will be offered', 'giant-checkout-offers-for-woocommerce') }}</span>
                </p>
              </div>

              <!-- Discount -->
              <div>
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">{{ __('Discount', 'giant-checkout-offers-for-woocommerce') }}</label>
                <div class="tw-flex tw-gap-4 tw-items-center">
                  <div class="tw-inline-flex tw-rounded-lg tw-border tw-border-gray-300 tw-overflow-hidden">
                    <button
                      type="button"
                      @click="bumpForm.discountType = 'percentage'"
                      class="tw-px-4 tw-py-2.5 tw-text-sm tw-font-medium tw-transition-colors"
                      :class="bumpForm.discountType === 'percentage' ? 'tw-bg-blue-500 tw-text-white' : 'tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50'"
                    >%</button>
                    <button
                      type="button"
                      @click="bumpForm.discountType = 'fixed'"
                      class="tw-px-4 tw-py-2.5 tw-text-sm tw-font-medium tw-border-l tw-border-gray-300 tw-transition-colors"
                      :class="bumpForm.discountType === 'fixed' ? 'tw-bg-blue-500 tw-text-white tw-border-blue-500' : 'tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50'"
                    >$</button>
                  </div>
                  <input
                    v-model.number="bumpForm.discountValue"
                    type="number"
                    :min="0"
                    :max="bumpForm.discountType === 'percentage' ? 100 : 9999"
                    class="tw-w-32 tw-px-4 tw-py-2.5 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-text-gray-900 focus:tw-outline-none focus:tw-border-blue-500 focus:tw-ring-1 focus:tw-ring-blue-500 tw-transition-colors"
                  />
                  <span class="tw-text-sm tw-text-gray-500">{{ bumpForm.discountType === 'percentage' ? __('off regular price', 'giant-checkout-offers-for-woocommerce') : __('discount', 'giant-checkout-offers-for-woocommerce') }}</span>
                </div>
              </div>

              <!-- Position -->
              <div>
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">{{ __('Display Position', 'giant-checkout-offers-for-woocommerce') }}</label>
                <div
                  ref="positionTriggerRef"
                  @click="positionDropdownOpen = !positionDropdownOpen"
                  class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-text-gray-900 tw-bg-white tw-cursor-pointer tw-flex tw-items-center tw-justify-between tw-transition-colors"
                  :class="positionDropdownOpen ? 'tw-border-blue-500 tw-ring-1 tw-ring-blue-500' : ''"
                >
                  <span>{{ selectedPositionLabel }}</span>
                  <svg class="tw-w-4 tw-h-4 tw-text-gray-400 tw-transition-transform" :class="positionDropdownOpen ? 'tw-rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
                <Teleport to="body">
                  <div v-if="positionDropdownOpen" @click="closeDropdowns" class="tw-fixed tw-inset-0" style="z-index: 9998;"></div>
                  <div
                    v-if="positionDropdownOpen"
                    :style="dropdownStyle"
                    class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-lg tw-shadow-lg tw-overflow-hidden"
                  >
                    <div
                      v-for="opt in positionOptions"
                      :key="opt.value"
                      @click="selectPosition(opt)"
                      class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-2.5 tw-text-sm tw-transition-colors"
                      :class="[
                        opt.pro && !isPro ? 'tw-text-gray-400 tw-cursor-not-allowed' : 'tw-text-gray-700 tw-cursor-pointer hover:tw-bg-blue-50',
                        bumpForm.position === opt.value ? 'tw-bg-blue-50 tw-text-blue-600 tw-font-medium' : ''
                      ]"
                    >
                      <span class="tw-flex tw-items-center tw-gap-2">
                        <svg v-if="opt.pro && !isPro" class="tw-w-3.5 tw-h-3.5 tw-text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        {{ opt.label }}
                      </span>
                      <span
                        v-if="opt.pro && !isPro"
                        class="tw-text-[9px] tw-font-semibold tw-bg-red-500 tw-text-white tw-px-1.5 tw-py-px tw-rounded tw-uppercase tw-leading-tight"
                      >{{ __('Pro', 'giant-checkout-offers-for-woocommerce') }}</span>
                    </div>
                  </div>
                </Teleport>
              </div>
            </div>
          </ElTabPane>

          <!-- Conditions Tab -->
          <ElTabPane :label="__('Conditions', 'giant-checkout-offers-for-woocommerce')" name="conditions">
            <div class="tw-space-y-7 tw-pt-6 tw-pb-4">
              <!-- Display Condition -->
              <div>
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">{{ __('Show bump when', 'giant-checkout-offers-for-woocommerce') }}</label>
                <select
                  v-model="bumpForm.displayCondition"
                  class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-text-gray-900 tw-bg-white focus:tw-outline-none focus:tw-border-blue-500 focus:tw-ring-1 focus:tw-ring-blue-500 tw-transition-colors tw-appearance-none tw-cursor-pointer"
                  style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%239ca3af%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px;"
                >
                  <option v-for="opt in conditionOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>

              <!-- Specific Products -->
              <div v-if="bumpForm.displayCondition === 'products'">
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">{{ __('Select Products', 'giant-checkout-offers-for-woocommerce') }}</label>
                <div class="tw-border tw-border-gray-300 tw-rounded-lg tw-max-h-48 tw-overflow-y-auto">
                  <label
                    v-for="product in productOptions"
                    :key="product.id"
                    class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-cursor-pointer hover:tw-bg-gray-50 tw-border-b tw-border-gray-100 last:tw-border-b-0"
                  >
                    <input
                      type="checkbox"
                      :value="product.id"
                      v-model="bumpForm.selectedProducts"
                      class="tw-w-4 tw-h-4 tw-rounded tw-border-gray-300 tw-text-blue-500 focus:tw-ring-blue-500"
                    />
                    <img
                      v-if="product.image"
                      :src="product.image.thumbnail || product.image.src"
                      :alt="product.name"
                      class="tw-w-8 tw-h-8 tw-rounded tw-object-cover tw-border tw-border-gray-200 tw-flex-shrink-0"
                    />
                    <span class="tw-text-sm tw-text-gray-700 tw-truncate">{{ product.name }}</span>
                  </label>
                </div>
                <p class="tw-text-xs tw-text-gray-400 tw-mt-2">{{ __('Bump will show when any of these products are in the cart', 'giant-checkout-offers-for-woocommerce') }}</p>
              </div>

              <!-- Specific Categories -->
              <div v-if="bumpForm.displayCondition === 'categories'">
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">{{ __('Select Categories', 'giant-checkout-offers-for-woocommerce') }}</label>
                <div class="tw-border tw-border-gray-300 tw-rounded-lg tw-max-h-48 tw-overflow-y-auto">
                  <label
                    v-for="opt in categoryOptions"
                    :key="opt.value"
                    class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-cursor-pointer hover:tw-bg-gray-50 tw-border-b tw-border-gray-100 last:tw-border-b-0"
                  >
                    <input
                      type="checkbox"
                      :value="opt.value"
                      v-model="bumpForm.selectedCategories"
                      class="tw-w-4 tw-h-4 tw-rounded tw-border-gray-300 tw-text-blue-500 focus:tw-ring-blue-500"
                    />
                    <span class="tw-text-sm tw-text-gray-700">{{ opt.label }}</span>
                  </label>
                </div>
                <p class="tw-text-xs tw-text-gray-400 tw-mt-2">{{ __('Bump will show when products from these categories are in the cart', 'giant-checkout-offers-for-woocommerce') }}</p>
              </div>

              <!-- Cart Total -->
              <div v-if="bumpForm.displayCondition === 'cart_total'">
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">{{ __('Minimum Cart Total', 'giant-checkout-offers-for-woocommerce') }}</label>
                <div class="tw-relative">
                  <span class="tw-absolute tw-left-4 tw-top-1/2 tw--translate-y-1/2 tw-text-gray-500">$</span>
                  <input
                    v-model.number="bumpForm.minCartTotal"
                    type="number"
                    :min="0"
                    :step="10"
                    class="tw-w-full tw-pl-8 tw-pr-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-text-gray-900 focus:tw-outline-none focus:tw-border-blue-500 focus:tw-ring-1 focus:tw-ring-blue-500 tw-transition-colors"
                  />
                </div>
                <p class="tw-text-xs tw-text-gray-400 tw-mt-2">{{ __('Bump will show when cart total is above this amount', 'giant-checkout-offers-for-woocommerce') }}</p>
              </div>

              <!-- Divider -->
              <div class="tw-border-t tw-border-gray-200 tw-pt-6">
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-4">{{ __('Additional Options', 'giant-checkout-offers-for-woocommerce') }}</label>
                <label class="tw-flex tw-items-center tw-gap-3 tw-cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="bumpForm.excludeBumpProduct"
                    class="tw-w-5 tw-h-5 tw-rounded tw-border-gray-300 tw-text-blue-500 focus:tw-ring-blue-500"
                  />
                  <span class="tw-text-gray-600">{{ __("Don't show if bump product is already in cart", 'giant-checkout-offers-for-woocommerce') }}</span>
                </label>
              </div>
            </div>
          </ElTabPane>

          <!-- Design Tab -->
          <ElTabPane :label="__('Design', 'giant-checkout-offers-for-woocommerce')" name="design">
            <div class="tw-space-y-7 tw-pt-6 tw-pb-4">

              <!-- Template Selector -->
              <div>
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-1">{{ __('Template', 'giant-checkout-offers-for-woocommerce') }}</label>
                <p class="tw-text-xs tw-text-gray-400 tw-mb-3">
                  {{ isCartPosition ? __('Showing cart-compatible templates', 'giant-checkout-offers-for-woocommerce') : __('Showing checkout-compatible templates', 'giant-checkout-offers-for-woocommerce') }}
                </p>
                <div class="tw-grid tw-grid-cols-2 tw-gap-2">
                  <button
                    v-for="tpl in availableTemplates"
                    :key="tpl.id"
                    type="button"
                    @click="selectTemplate(tpl)"
                    class="tw-relative tw-flex tw-items-center tw-gap-3 tw-px-3 tw-py-2.5 tw-rounded-lg tw-border tw-text-left tw-transition-all"
                    :class="activeTemplate === tpl.id
                      ? 'tw-border-blue-500 tw-bg-blue-50 tw-ring-1 tw-ring-blue-500'
                      : 'tw-border-gray-200 tw-bg-white hover:tw-border-gray-300 hover:tw-bg-gray-50'"
                  >
                    <span
                      class="tw-w-7 tw-h-7 tw-rounded-md tw-flex-shrink-0 tw-border tw-border-white tw-shadow-sm"
                      :style="{ backgroundColor: tpl.defaultStyles.buttonColor }"
                    ></span>
                    <span class="tw-text-sm tw-font-medium tw-text-gray-800">{{ tpl.name }}</span>
                    <span
                      v-if="tpl.isPro && !isPro"
                      class="tw-absolute tw-top-1 tw-right-1 tw-text-[9px] tw-font-bold tw-bg-red-500 tw-text-white tw-px-1.5 tw-py-px tw-rounded tw-uppercase tw-leading-tight"
                    >{{ __('PRO', 'giant-checkout-offers-for-woocommerce') }}</span>
                    <span v-if="activeTemplate === tpl.id" class="tw-ml-auto tw-text-blue-500">
                      <svg class="tw-w-4 tw-h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                      </svg>
                    </span>
                  </button>
                </div>
              </div>

              <!-- Title -->
              <div>
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">{{ __('Bump Title', 'giant-checkout-offers-for-woocommerce') }}</label>
                <input
                  v-model="bumpForm.title"
                  type="text"
                  :placeholder="__('e.g., Wait! Add this to your order', 'giant-checkout-offers-for-woocommerce')"
                  class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-text-gray-900 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-border-blue-500 focus:tw-ring-1 focus:tw-ring-blue-500 tw-transition-colors"
                />
              </div>

              <!-- Description -->
              <div>
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">{{ __('Description', 'giant-checkout-offers-for-woocommerce') }}</label>
                <textarea
                  v-model="bumpForm.description"
                  rows="3"
                  :placeholder="__('Describe your offer...', 'giant-checkout-offers-for-woocommerce')"
                  class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-text-gray-900 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-border-blue-500 focus:tw-ring-1 focus:tw-ring-blue-500 tw-transition-colors tw-resize-none"
                ></textarea>
              </div>

              <!-- Button Text -->
              <div>
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">{{ __('Button/Checkbox Text', 'giant-checkout-offers-for-woocommerce') }}</label>
                <input
                  v-model="bumpForm.buttonText"
                  type="text"
                  :placeholder="__('e.g., Yes, Add to Order!', 'giant-checkout-offers-for-woocommerce')"
                  class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-text-gray-900 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-border-blue-500 focus:tw-ring-1 focus:tw-ring-blue-500 tw-transition-colors"
                />
              </div>
            </div>
          </ElTabPane>
        </ElTabs>
      </div>

      <!-- Right Panel: Live Preview -->
      <div class="tw-w-1/2 tw-bg-gray-100 tw-rounded-2xl tw-p-8 tw-overflow-y-auto">
        <div class="tw-text-center tw-mb-8">
          <span class="tw-inline-block tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-widest tw-bg-white tw-px-4 tw-py-1.5 tw-rounded-full tw-border tw-border-gray-200 tw-shadow-sm">{{ __('Live Preview', 'giant-checkout-offers-for-woocommerce') }}</span>
        </div>

        <div class="tw-flex tw-items-center tw-justify-center tw-flex-1 tw-min-h-0">
          <div class="preview-full-width tw-w-full">
            <component
              :is="previewComponents[activeTemplate]"
              :title="previewTitle"
              :product-name="selectedProductName"
              :description="bumpForm.description"
              :button-text="bumpForm.buttonText"
              :discount-value="bumpForm.discountValue"
              :discount-type="bumpForm.discountType"
              :image="selectedProductImage"
              :styles="currentTemplateStyles"
            />
          </div>
        </div>

        <div class="tw-text-center">
          <p class="tw-text-sm tw-text-gray-400 tw-m-0">{{ __('This is how your bump will appear at checkout', 'giant-checkout-offers-for-woocommerce') }}</p>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <template #footer>
      <div class="tw-flex tw-items-center tw-justify-between tw-w-full">
        <ElButton size="large" @click="closeModal" class="tw-px-6">{{ __('Cancel', 'giant-checkout-offers-for-woocommerce') }}</ElButton>
        <div class="tw-flex tw-gap-4">
          <ElButton size="large" @click="saveBump('draft')" class="tw-px-6">{{ __('Save as Draft', 'giant-checkout-offers-for-woocommerce') }}</ElButton>
          <ElButton type="primary" size="large" @click="saveBump('active')" class="tw-px-6">
            <Plus v-if="!isEditMode" class="tw-w-4 tw-h-4 tw-mr-2" />
            {{ isEditMode ? __('Update Bump', 'giant-checkout-offers-for-woocommerce') : __('Create Bump', 'giant-checkout-offers-for-woocommerce') }}
          </ElButton>
        </div>
      </div>
    </template>
  </ElDialog>
</template>

<style scoped>
:deep(.bump-modal) {
  --el-dialog-border-radius: 16px;
}

:deep(.bump-modal .el-dialog__header) {
  padding: 24px 28px;
  border-bottom: 1px solid #e5e7eb;
  margin: 0;
}

:deep(.bump-modal .el-dialog__body) {
  padding: 28px;
}

:deep(.bump-modal .el-dialog__footer) {
  padding: 20px 28px;
  border-top: 1px solid #e5e7eb;
}

:deep(.bump-tabs .el-tabs__header) {
  margin-bottom: 0;
}

:deep(.bump-tabs .el-tabs__nav-wrap::after) {
  height: 1px;
}

:deep(.bump-tabs .el-tabs__item) {
  font-weight: 500;
  font-size: 15px;
  padding: 0 24px;
  height: 48px;
}

:deep(.bump-tabs .el-tabs__item.is-active) {
  font-weight: 600;
}

.preview-full-width :deep(> div) {
  max-width: 100% !important;
  width: 100% !important;
}

.preview-full-width :deep(> div > div) {
  transform: none !important;
  max-width: 100% !important;
}
</style>
