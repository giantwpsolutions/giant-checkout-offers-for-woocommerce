<script setup>
defineProps({
  title: { type: String, default: 'Complete Your Order' },
  productName: { type: String, default: 'Product Name' },
  description: { type: String, default: '' },
  buttonText: { type: String, default: 'Add & Continue' },
  price: { type: String, default: '$9.99' },
  originalPrice: { type: String, default: '' },
  discountValue: { type: Number, default: 10 },
  discountType: { type: String, default: 'percentage' },
  image: { type: String, default: '' },
  styles: { type: Object, default: () => ({}) },
});
</script>

<template>
  <div class="tw-w-full tw-max-w-[280px]">
    <div
      class="tw-shadow-md tw-scale-90"
      :style="{ backgroundColor: styles.backgroundColor, borderWidth: (styles.borderWidth || 1) + 'px', borderStyle: styles.borderStyle || 'solid', borderColor: styles.borderColor, borderRadius: (styles.borderRadius || 8) + 'px', padding: (styles.padding || 12) + 'px' }"
    >
      <div class="tw-text-center tw-mb-2">
        <span class="tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider" :style="{ color: styles.accentColor || '#8b5cf6' }">{{ title }}</span>
        <h4 class="tw-font-bold tw-text-sm tw-mt-0.5 tw-mb-0" :style="{ color: styles.textColor }">{{ productName }}</h4>
        <p v-if="description" class="tw-text-xs tw-mt-0.5 tw-mb-0" :style="{ color: styles.descriptionColor || '#6b7280' }">{{ description }}</p>
      </div>
      <div class="tw-space-y-1.5">
        <div class="tw-flex tw-items-center tw-gap-2">
          <div class="tw-w-5 tw-h-5 tw-rounded-full tw-text-white tw-flex tw-items-center tw-justify-center tw-text-[10px] tw-font-bold tw-flex-shrink-0" :style="{ backgroundColor: styles.accentColor || '#8b5cf6' }">1</div>
          <div class="tw-flex-1 tw-h-0.5 tw-rounded" :style="{ backgroundColor: (styles.accentColor || '#8b5cf6') + '60' }"></div>
          <div class="tw-w-5 tw-h-5 tw-rounded-full tw-text-white tw-flex tw-items-center tw-justify-center tw-text-[10px] tw-font-bold tw-flex-shrink-0" :style="{ backgroundColor: styles.accentColor || '#8b5cf6' }">2</div>
          <div class="tw-flex-1 tw-h-0.5 tw-bg-gray-200 tw-rounded"></div>
          <div class="tw-w-5 tw-h-5 tw-rounded-full tw-bg-gray-200 tw-text-gray-400 tw-flex tw-items-center tw-justify-center tw-text-[10px] tw-font-bold tw-flex-shrink-0">3</div>
        </div>
      </div>
      <div class="tw-mt-2 tw-p-2 tw-rounded tw-border" :style="{ backgroundColor: (styles.accentColor || '#8b5cf6') + '10', borderColor: (styles.accentColor || '#8b5cf6') + '25' }">
        <div class="tw-flex tw-items-center tw-justify-between">
          <div class="tw-flex tw-items-center tw-gap-2">
            <div class="tw-w-6 tw-h-6 tw-rounded tw-flex tw-items-center tw-justify-center tw-overflow-hidden" :style="{ backgroundColor: (styles.accentColor || '#8b5cf6') + '20' }">
              <img v-if="image" :src="image" :alt="productName" class="tw-w-full tw-h-full tw-object-cover" />
              <svg v-else class="tw-w-3 tw-h-3" :style="{ color: styles.accentColor || '#8b5cf6' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
              <p class="tw-font-semibold tw-text-xs tw-m-0" :style="{ color: styles.textColor }">2-Year Warranty</p>
            </div>
          </div>
          <span class="tw-font-bold tw-text-sm" :style="{ color: styles.priceColor || '#7c3aed' }">+{{ price }}</span>
        </div>
      </div>
      <button class="tw-w-full tw-mt-2 tw-font-semibold tw-py-1.5 tw-rounded tw-text-xs" :style="{ backgroundColor: styles.buttonColor, color: styles.buttonTextColor || '#ffffff' }">{{ buttonText }}</button>
    </div>
  </div>
</template>
